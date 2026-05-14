<?php
namespace Abbott\SecureAdmin\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\User\Model\UserFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Class DeactivateMboUserNotification
 * Abbott\SecureAdmin\Cron
 *
 */
class DeactivateMboUserNotification
{

    private const NOTIFICATION_BEFORE = 'secure_admin_configuration/deactivatembouserconfig/password_expiration_notification_before';
    private const NOTIFICATION_PERIOD = 'secure_admin_configuration/deactivatembouserconfig/password_expiration_period';
    private const MODULE_STATUS = 'secure_admin_configuration/deactivatembouserconfig/enabled_module';
    private const IDENT_SUPPORT_NAME ='trans_email/ident_support/name';
    private const IDENT_SUPPORT_EMAIL ='trans_email/ident_support/email';

    private const EMAIL_IDENTIFIER_NOTIFICATION = 'secure_admin_configuration/deactivatembouserconfig/template_identifier_notification';
    private const EMAIL_IDENTIFIER_DEACTIVATE = 'secure_admin_configuration/deactivatembouserconfig/template_identifier_deactivate';
    private const DAYS = 'days';
 
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Store code
     */
    protected $storeCode;

    /**
     * @var Directory List
     */
    protected $dirList;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var userFactory
     */
    protected $userFactory;

    /**
     * @var logger
     */
    protected $logger;

    /**
     * @var Magento\Backend\Helper\Data
     */
    protected $helperBackend;

    /**
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Abbott\SecureAdmin\Logger\MboUserDeactivateLogger $logger
     * @param \Magento\Backend\Helper\Data $helperBackend
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        UserFactory $userFactory,
        \Abbott\SecureAdmin\Logger\MboUserDeactivateLogger $logger,
        \Magento\Backend\Helper\Data $helperBackend
    ) {
        $this->storeManager     = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->userFactory = $userFactory;
        $this->logger = $logger;
        $this->helperBackend = $helperBackend;
    }
    
    /**
     * Function to send notification to MBO user if not logged specified time period
     *
     * @param none
     * @return none
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->logger->info('******* MBO user Deactivation Cron Started ********');
        if ($this->scopeConfig->getValue(
            self::MODULE_STATUS,
            ScopeInterface::SCOPE_STORE
        )) {
    
            // call function to deactivate admin user if not logged specified time
            $this->getAndDeactivateAdminUsers();

           // call function to send deactivate notification to admin user if not logged specified before time
            $this->getAndSendNotificationToAdminUsers();
            
        } else {
            $this->logger->info('Module is not Enable.');
        }
        /****** get admin user using factory */
    }

    /**
     * Function to get admin user
     */
    public function getAndDeactivateAdminUsers()
    {
        $this->logger->info('***** User Deactivation Started *****');

        $passwordDeactivateDays = $this->scopeConfig->getValue(
            self::NOTIFICATION_PERIOD,
            ScopeInterface::SCOPE_STORE
        );
        $this->logger->info('passwordDeactivateDays = '. $passwordDeactivateDays);

        $tillActive = date("Y-m-d H:i:s", strtotime(date('Y-m-d H:i:s')." -".$passwordDeactivateDays.self::DAYS));
        $tilActiveWithoutLogin = date("Y-m-d", strtotime(date($tillActive)));
        $this->logger->info('tilActiveWithoutLogin = '. $tilActiveWithoutLogin);

        $template = $this->scopeConfig->getValue(
            self::EMAIL_IDENTIFIER_DEACTIVATE,
            ScopeInterface::SCOPE_STORE
        );
        $emailData['template'] = $template;
        $emailData['url'] = $this->helperBackend->getHomePageUrl();

        // call function to get admin user which is active and not logged till specified days
        $adminUsersData = $this->getAdminUsers($tilActiveWithoutLogin);
        $this->logger->info('Total adminUsersData  = '. $adminUsersData->getSize());
        if ($adminUsersData->getSize()) {
            $totalUserDeactivate = 0;
            foreach ($adminUsersData as $adminUsers) {
                $adminUser = $adminUsers->getData();

                // call fucntion to deactivate admin user
                $this->deactivateAdminUser($adminUser['user_id']);

                $emailData['name'] = $adminUser['firstname'].' '.$adminUser['lastname'];
                $this->logger->info('username  = '.$emailData['name']);

                $emailData['passwordDeactivateDays'] = $passwordDeactivateDays;
                $emailData['email'] = $adminUser['email'];

                // call funtion to send email
                $this->sendEmail($emailData);
                $this->logger->info('Deactivated Notification Sent to user  = '. $emailData['email']);
                $totalUserDeactivate++;
            }
            $this->logger->info('totalUserDeactivated  = '. $totalUserDeactivate);
        } else {
            $this->logger->info('No MBO user found to deactivate');
        }
    }

    /**
     * Function to get admin to send notification
     */
    public function getAndSendNotificationToAdminUsers()
    {
        $this->logger->info('***** User Notification send Started *****');
        $passwordNotificationDays = $this->scopeConfig->getValue(
            self::NOTIFICATION_BEFORE,
            ScopeInterface::SCOPE_STORE
        );
        $this->logger->info('passwordNotificationDays = '. $passwordNotificationDays);

        $passwordDeactivateDays = $this->scopeConfig->getValue(
            self::NOTIFICATION_PERIOD,
            ScopeInterface::SCOPE_STORE
        );
        $this->logger->info('passwordDeactivateDays = '. $passwordDeactivateDays);
                          
        $emailData['notloggeddays'] = $passwordDeactivateDays - $passwordNotificationDays;
        $this->logger->info('notLoggedDays = '. $emailData['notloggeddays']);

        $tillNotLoggedDate = date("Y-m-d", strtotime(date('Y-m-d')." -".$emailData['notloggeddays'].self::DAYS));
        $this->logger->info('tillNotLoggedDate = '. $tillNotLoggedDate);

        $deactivateDate = date('Y-m-d', strtotime("+" . $passwordNotificationDays .self::DAYS));
        $this->logger->info('Deactivation date  = '.$deactivateDate);

        $template = $this->scopeConfig->getValue(
            self::EMAIL_IDENTIFIER_NOTIFICATION,
            ScopeInterface::SCOPE_STORE
        );
        $emailData['template'] = $template;
        $emailData['url'] = $this->helperBackend->getHomePageUrl();

        // call function to get admin user which is active and not logged till active date
        $adminUsersData = $this->getAdminUsersForNotification($tillNotLoggedDate);
        $this->logger->info('Total adminUsersData  = '. $adminUsersData->getSize());
        if ($adminUsersData->getSize()) {
            $totalNotificationSent = 0;
            foreach ($adminUsersData as $adminUsers) {
                $adminUser = $adminUsers->getData();

                $emailData['name'] = $adminUser['firstname'].' '.$adminUser['lastname'];
                $this->logger->info('username  = '.$emailData['name']);

                $emailData['passwordNotificationDays'] = $passwordNotificationDays;
                
                $emailData['email'] = $adminUser['email'];
            
                // call funtion to send email
                $this->sendEmail($emailData);
                $this->logger->info('Notification Sent to user  = '. $emailData['email']);
                $totalNotificationSent++;
            }
            $this->logger->info('totalNotificationSent  = '. $totalNotificationSent);
        } else {
            $this->logger->info('No MBO user found to send notification');
        }
    }
    
    /**
     * Function to get admin user
     *
     * @param string $tilActiveWithoutLogin
     * @return collection
     */
    public function getAdminUsers($tilActiveWithoutLogin)
    {
        $collection = $this->userFactory->create()->getCollection()->addFieldToSelect(
            ['user_id','email','firstname','lastname']
        );
        $collection->addFieldToFilter('is_active', '1');
        $collection->addFieldToFilter('logdate', [['gteq' => $tilActiveWithoutLogin.' 00:00:00']]);
        $collection->addFieldToFilter('logdate', [['lteq' => $tilActiveWithoutLogin.' 23:59:59']]);
        return $collection;
    }

    /**
     * Function to get admin user
     *
     * @param string $tillNotLoggedDate
     * @return collection
     */
    public function getAdminUsersForNotification($tillNotLoggedDate)
    {
        $collection = $this->userFactory->create()->getCollection()->addFieldToSelect(
            ['user_id','email','firstname','lastname']
        );
        $collection->addFieldToFilter('is_active', '1');
        $collection->addFieldToFilter('logdate', [['gteq' => $tillNotLoggedDate]]);
        $collection->addFieldToFilter('logdate', [['lteq' => $tillNotLoggedDate.' 23:59:59']]);
        return $collection;
    }

    /**
     * Function to deactivate admin user
     *
     * @param int $userId
     */
    public function deactivateAdminUser($userId)
    {
        $this->logger->info('userId  = '. $userId);
        $adminUserData = $this->userFactory->create()->load($userId);
        $adminUserData->setIsActive(0);
        $adminUserData->save();
        $this->logger->info('User deactivated with userId = '.$userId);
    }
    
    /**
     * Function to send email with link to mbo users
     *
     * @param array $emailData
     * @return none
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendEmail($emailData)
    {
        $sender = [
         'name' => $this->scopeConfig->getValue(
             self::IDENT_SUPPORT_NAME,
             ScopeInterface::SCOPE_STORE
         ),
         'email' => $this->scopeConfig->getValue(
             self::IDENT_SUPPORT_EMAIL,
             ScopeInterface::SCOPE_STORE
         )
        ];

        // Send Mail
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
           ->setTemplateIdentifier($emailData['template'])
           ->setTemplateOptions(
               [
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
               ]
           )
           ->setTemplateVars($emailData)
           ->setFrom($sender)
           ->addTo($emailData['email'])
           ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();

        unset($emailData);
        unset($sender);
    }
}