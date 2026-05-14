<?php
namespace Abbott\Customerhistory\Block\Adminhtml\Edit\Tab;

use Abbott\Customerhistory\Model\ResourceModel\Customerhistory\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;

class Customerhistory extends \Magento\Framework\View\Element\Template implements TabInterface
{
     public $collectionFactory;
    public $backendHelper;
    public $backendSession;
    /**
      * Core registry
      *
      * @var Registry
      */
    protected $coreRegistry;

    /**
     * Construct function
     *
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $backendHelper
     * @param Session $backendSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $collectionFactory,
        UrlInterface $backendHelper,
        Session $backendSession,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->backendHelper = $backendHelper;
        $this->backendSession = $backendSession;
        parent::__construct($context, $data);
    }

    /**
     * Get Customer ID
     *
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get Tab Label
     *
     * @return Phrase
     */
    public function getTabLabel(): Phrase
    {
        return $this->getTabTitle();
    }

    /**
     * Get Tab Title
     *
     * @return Phrase
     */
    public function getTabTitle(): Phrase
    {
        return __('Customer History');
    }

    /**
     * Check Can Show Tab
     *
     * @return bool
     */
    public function canShowTab(): bool
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * Check isHidden
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass(): string
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl(): string
    {
        return $this->getUrl('customerhistory/*/customerhistory', ['_current' => true]);
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded(): bool
    {
        return false;
    }

    /**
     * Get Save URL
     *
     * @return string
     */
    public function getSaveUrl(): string
    {
        return $this->backendHelper->getUrl('customerhistory/index/customerhistory');
    }

    /**
     * To get Admin UserData
     *
     * @return array
     */
    public function getAdminUserData(): array
    {
        return $this->backendSession->getUser()->getData();
    }

    /**
     * To getCustomer Info
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerInfo(int $customerId): array
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('customer_id', $customerId);
        return $collection->getData();
    }

    /**
     * Get Flag Text
     *
     * @param string $flag
     * @return string
     */
    public function getFlagText(string $flag): string
    {
        $info = '';
        switch ($flag) {
            case 'new_customer_create':
                $info = "Account Information Added";
                break;
            case 'account_information_updated':
                $info = "Account Information Updated";
                break;
            case 'new_address_information':
                $info = "Address Information Added";
                break;
            case 'address_information_updated':
                $info = "Address Information Updated";
                break;
            case 'address_deleted':
                $info = "Address Information Deleted";
                break;
            case 'credit_card_updated':
                $info = "Credit Card Information Updated";
                break;
            case 'credit_card_deleted':
                $info = "Credit Card Information Deleted";
                break;
            case 'comments':
                $info = "Comments Added";
                break;
            default:
                break;
        }
        return $info;
    }
}
