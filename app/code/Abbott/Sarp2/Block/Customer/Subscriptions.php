<?php

namespace Abbott\Sarp2\Block\Customer;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Plan\TitleResolver;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection;
use Magento\Framework\Data\Form\FormKey;

class Subscriptions extends \Aheadworks\Sarp2\Block\Customer\Subscriptions
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $orderModel;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var FormKey
     */
    private $formKey;
    
    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param ProfileManagementInterface $profileManagement
     * @param StatusSource $statusSource
     * @param ProductRepositoryInterface $productRepository
     * @param ProductUrl $productUrl
     * @param Session $customerSession
     * @param CurrencyFactory $currencyFactory
     * @param ActionPermission $actionPermission
     * @param PlanRepositoryInterface $planRepository
     * @param TitleResolver $titleResolver
     * @param \Magento\Sales\Model\Order $orderModel
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        ProfileManagementInterface $profileManagement,
        StatusSource $statusSource,
        ProductRepositoryInterface $productRepository,
        ProductUrl $productUrl,
        Session $customerSession,
        CurrencyFactory $currencyFactory,
        ActionPermission $actionPermission,
        PlanRepositoryInterface $planRepository,
        TitleResolver $titleResolver,
        \Magento\Sales\Model\Order $orderModel,
        FormKey $formKey,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $collectionFactory,
            $profileManagement,
            $statusSource,
            $productRepository,
            $productUrl,
            $customerSession,
            $currencyFactory,
            $actionPermission,
            $planRepository,
            $titleResolver,
            $data
        );
        $this->orderModel = $orderModel;
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
    }
    
    public function getRealOrderId(\Aheadworks\Sarp2\Api\Data\ProfileInterface $profile)
    {
        $orderId = $profile->getLastOrderId();
        $realOrderId = $this->orderModel->load($orderId)->getRealOrderId();
        return $realOrderId;
    }
    
    public function getViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    //ANAPOLLO-7335 Starts
    /**
     * Get profiles
     *
     * @return Collection|null
     */
    public function getProfilesList()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();
            $this->collection
                ->addFieldToFilter(
                    ProfileInterface::CUSTOMER_ID,
                    ['eq' => $this->customerSession->getCustomerId()]
                )->setPageSize(2)
                ->addOrder(ProfileInterface::CREATED_AT, Collection::SORT_ORDER_DESC);
        }
        return $this->collection;
    }

    /**
     * Get cancel profile url
     *
     * @param int $profileId
     * @return string
     */
    public function getCancelDashboardUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile/cancel',
            [
                'profile_id' => $profileId,
                'return_to'  => 'customer_dashboard' ,
                'form_key' => $this->formKey->getFormKey()
            ]
        );
    }
    //ANAPOLLO-7335 Ends
}
