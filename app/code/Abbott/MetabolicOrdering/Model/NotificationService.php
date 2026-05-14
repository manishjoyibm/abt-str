<?php
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Model;

use Abbott\MetabolicOrdering\Helper\Data as Config;
use Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic as MetabolicResource;
use Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Cron job: Sends expiry reminders to customers for products that will
 * expire after a configured number of days from the current store date.
 *
 * Flow:
 * 1) Check feature toggle from config
 * 2) Compute store-local target date = today + N days (using TimezoneInterface)
 * 3) Pull records with qty > 0, expiry_date == target date, not emailed yet
 * 4) Send email using a template and mark items as emailed with UTC timestamp
 */
class NotificationService
{
    /** @var Config */
    protected $config;

    /** @var LoggerInterface */
    protected $logger;

    /** @var CollectionFactory */
    protected $collectionFactory;

    /** @var MetabolicResource */
    protected $resourceModel;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var TransportBuilder */
    protected $transportBuilder;

    /** @var StateInterface */
    protected $inlineTranslation;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;



    /**
     * DI Constructor.
     *
     * @param StoreManagerInterface        $storeManager
     * @param ScopeConfigInterface         $scopeConfig
     * @param Config                       $config
     * @param LoggerInterface              $logger
     * @param TransportBuilder             $transportBuilder
     * @param CollectionFactory            $collectionFactory
     * @param MetabolicResource            $resourceModel
     * @param StateInterface               $inlineTranslation
     * @param CustomerRepositoryInterface  $customerRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Config $config,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        CollectionFactory $collectionFactory,
        MetabolicResource $resourceModel,
        StateInterface $inlineTranslation,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->storeManager       = $storeManager;
        $this->scopeConfig        = $scopeConfig;
        $this->config             = $config;
        $this->logger             = $logger;
        $this->transportBuilder   = $transportBuilder;
        $this->collectionFactory  = $collectionFactory;
        $this->resourceModel      = $resourceModel;
        $this->inlineTranslation  = $inlineTranslation;
        $this->customerRepository = $customerRepository;
    }

  
    /**
     * Send the "expiry" reminder email for a metabolic-ordering item.
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $item
     *        Metabolic ordering row model (must provide getData()/setData() for
     *        'customer_email', 'sku', 'qty', 'expiry_date', 'expiry_email_sent', 'expiry_email_sent_at').
     *
     * @return void
     */
    public function sendExpiryEmail($item)
    {
        try {
                // Recipient email
                    $email = (string)$item->getData('customer_email');
                    if (!$email) {
                        return;
                    }

                    // Email template identifier with fallback
                    $templateId = trim((string)$this->config->expiryTemplate()) ?: 'abbott_metabolic_expiry_template';

                    // Base email variables shared across sends
                    $baseEmailVars = [
                        'template' => $templateId,
                        // Expecting ['name' => '...', 'email' => '...']
                        'sender'   => $this->config->expirySender(),
                    ];
                    // Resolve display name; default if lookup fails
                    $customerName = $this->getCustomerName($email);

                    // Prepare template vars
                    $sku        = (string)$item->getData('sku');
                    $qty        = (string)$item->getData('qty');
                    $expiryDate = (string)$item->getData('expiry_date');

                    $emailVars = $baseEmailVars;
                    $emailVars['email']         = $email;
                    $emailVars['customer_name'] = $customerName;
                    $emailVars['sku']           = $sku;
                    $emailVars['expiry_date']   = $expiryDate;
                    $emailVars['qty']           = $qty;

                    try {
                        // Send the email
                        $this->sendEmail($emailVars);

                        // Mark as sent with UTC timestamp using Magento DateTime service
                        $item->setData('expiry_email_sent', 1);
                        $item->setData('expiry_email_sent_at', $this->config->getCurrentTime());

                        // Persist changes
                        $this->resourceModel->save($item);

                        $this->logger->info("Sent Expiry email to {$email} for SKU {$sku}");
                    } catch (\Exception $e) {
                        // Log and continue with next record
                        $this->logger->info("Error sending expiry email to {$email}: " . $e->getMessage());
                    }
            }
            catch (\Exception $e) {
            // Top-level exception to avoid disrupting the cron schedule
            $this->logger->info('[Abbott_MetabolicOrdering] Cron failure in SendExpiryReminders: ' . $e->getMessage());
        }
    }


    /**
     * Send the "run-out / threshold" email for a metabolic-ordering item.
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $item
     *        Metabolic ordering row model (must provide getData()/setData() for
     *        'customer_email', 'sku', 'expiry_date', 'threshold_email_sent', 'threshold_email_sent_at').
     *
     * @return void
     */
     public function sendRunOutEmail($item)
        {
        try {
                $email = trim((string)$item->getData('customer_email'));
                if ($email === '') {
                    return;
                }

                // Resolve customer display name, defaulting if lookup fails
               $customerName = $this->getCustomerName($email);

                $templateId = (string)$this->config->thresholdTemplate();
                $sender = (string)$this->config->thresholdSender();

                // Prepare common email data (template id)
                $emailData = [
                    'template' => $templateId
                ];

                // Prepare email template variables
                $emailData['email']         = $email;
                $emailData['customer_name'] = $customerName;
                $emailData['sku']           = (string)$item->getData('sku');;
                $emailData['expiry_date']   = (string)$item->getData('expiry_date');
                $emailData['sender'] = $sender;

                try {
                    // Send the threshold email
                    $this->sendEmail($emailData);

                    // Mark as sent with UTC timestamp using Magento DateTime service
                    $item->setData('threshold_email_sent', 1);
                    $item->setData('threshold_email_sent_at', $this->config->getCurrentTime());

                    // Persist the changes
                    $this->resourceModel->save($item);

                    $this->logger->info("Sent Threshold email to {$email} for SKU {$sku}");
                } catch (\Exception $e) {
                    $this->logger->info("Error sending threshold email to {$email}: " . $e->getMessage());
                }
            }
            catch (\Exception $e) {
            // Top-level exception to avoid disrupting the cron schedule
            $this->logger->info('[Abbott_MetabolicOrdering] Cron failure in SendExpiryReminders: ' . $e->getMessage());
        }
    }
    
    
    /**
     * Retrieve the full customer name by email address.
     *
     * Attempts to load the customer using the CustomerRepository and returns
     * the concatenated first and last name. If the name is empty or the lookup
     * fails, returns a fallback string ("Valued Customer").
     *
     * @param string $email Customer email address.
     * @return string Full name if available, otherwise "Valued Customer".
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the customer does not exist (caught internally).
     */
    public function getCustomerName($email)
    {
         try {
                $customer     = $this->customerRepository->get($email);
                $fullName     = trim($customer->getFirstName() . ' ' . $customer->getLastName());
                return  $customerName = $fullName !== '' ? $fullName : $customerName;
                } 
        catch (\Exception $e) {
                    return 'Valued Customer';
                }
    }

    /**
     * Sends reminder email using Magento's TransportBuilder.
     *
     * Expects the following keys in $emailData:
     * - template (string): Template identifier
     * - sender   (array) : Sender array ['name' => string, 'email' => string]
     * - email    (string): Recipient address
     * - Additional template vars (e.g., customer_name, sku, expiry_date, qty)
     *
     * @param array<string, mixed> $emailData
     * @return void
     *
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendEmail(array $emailData): void
    {
        // Sender from module configuration
        $sender = $emailData['sender'];

        // Suspend inline translation during email rendering
        $this->inlineTranslation->suspend();

        // Build and dispatch the message
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($emailData['template'])
            ->setTemplateOptions([
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => Store::DEFAULT_STORE_ID,
            ])
            ->setTemplateVars($emailData)
            ->setFrom($sender)
            ->addTo($emailData['email'])
            ->getTransport();

        $transport->sendMessage();

        // Resume inline translation
        $this->inlineTranslation->resume();

        // Optional cleanup (clarity)
        unset($emailData, $sender);
    }
}