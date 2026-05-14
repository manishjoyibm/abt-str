<?php
namespace Abbott\Sarp2\Block\Customer\Subscriptions\Edit;

class VaultList extends \Magento\Framework\View\Element\Template
{
    public $paymentTokenManagement;
    protected $customerSession;

    protected $paymenttokenmanagement;

    protected $json;

    protected $logger;

    protected $formKey;

    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\App\Request\Http $request,
        array $data = []) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->json = $json;
        $this->logger = $logger;
        $this->formKey = $formKey;
        $this->request = $request;

    }

    /**
     * get Customer Vaults
     *
     * @return void
     */
    public function getCustomerVaults()
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getCustomer()->getId();
                $cardList = $this->paymentTokenManagement->getListByCustomerId($customerId);
                return $cardList;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return false;

    }

    /**
     *
     * @param JSON $data
     * @return Arary
     */
    public function getCardDetailsArray($data = null)
    {
        try {
            if ($data) {
                $cardData = $this->json->unserialize($data);
                return $cardData;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     *
     *
     * @return int
     */
    public function getProfileId()
    {

        return $this->request->getParam('profile_id');
    }

}
