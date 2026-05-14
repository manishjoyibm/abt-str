<?php

namespace Abbott\Sarp2\Block\Checkout\Onepage;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Abbott\GigyaIM\Helper\Data as GigyaHelper;
use Abbott\Sarp2\Helper\Data as DataHelper;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory as ProfilecollectionFactory;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    public $orderFactory;
    public $productRepositoryFactory;
    public $productCollectionFactory;
    public $productHelper;
    /**
    * @var \Magento\Framework\Serialize\Serializer\Json
    */
    protected $_json;
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;
    /**
     * @var CollectionFactory
     */
    /**
     * \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;
    private $profileCollectionFactory;
    /**
     * @var gigyaHelper
     */
    protected $gigyaHelper;
     /**
     * @var dataHelper
     */
    protected $dataHelper;


    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    protected $customer;


    /**
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $orderConfig
     * @param HttpContext $httpContext
     * @param ProfileRepositoryInterface $profileRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderInterface $orderFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        ProfileRepositoryInterface $profileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderInterface $orderFactory,
	DataHelper $dataHelper,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        ProfilecollectionFactory $profileCollectionFactory,
        GigyaHelper $gigyaHelper,
        \Magento\Customer\Model\Session $customer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->profileRepository = $profileRepository;
        $this->orderFactory = $orderFactory;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productHelper = $productHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->gigyaHelper = $gigyaHelper;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->customer = $customer;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->_eavConfig = $eavConfig;
        $this->dataHelper = $dataHelper;
        $this->_json = $json;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareBlockData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getIncrementId()) {
            parent::prepareBlockData();
        }
        $this->addData(['can_view_profiles' => true]);
    }

    /**
     * Get profiles
     *
     * @return ProfileInterface[]
     */
    public function getProfiles()
    {
        $profiles = [];
        $profileIds = $this->_checkoutSession->getLastProfileIds();
        if ($profileIds) {
            $this->_checkoutSession->setLastProfileIds(null);
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(ProfileInterface::PROFILE_ID, $profileIds, 'in')
                ->create();
            $profiles = $this->profileRepository->getList($searchCriteria)
                ->getItems();
        }

        $customerId = $this->customer->getId();
        $abt_usr = $this->_json->unserialize($this->gigyaHelper->getCustomCookie('abt_usr') ?? '{}', true);
        if ($this->getCustomerSubscription($customerId) > 0) {
            $abt_usr['magento_page']['subscriptions'] = 1;
        }

        $this->gigyaHelper->setCookie('abt_usr', $this->_json->serialize($abt_usr));
        return $profiles;
    }

    /**
     * Get view profile url
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getViewProfileUrl($profile)
    {
        return $this->getUrl(
            'aw_sarp2/profile_edit/index',
            ['profile_id' => $profile->getProfileId()]
        );
    }

    public function getOrderIdFromSession()
    {
        return $this->_checkoutSession->getData('last_order_id');
    }

    public function getOrderData()
    {
        $orderId = $this->_checkoutSession->getData('last_order_id');
        return $this->orderFactory->load($orderId);
    }

    public function getOrderTotals()
    {
        $orderData = $this->getOrderData();
        $totals = [];
        $totals['subtotal'] = new \Magento\Framework\DataObject(
            ['code' => 'subtotal', 'value' => $orderData->getSubtotal(), 'label' => __('Subtotal')]
        );
        if ((double)$orderData->getDiscountAmount() != 0) {
            if ($orderData->getDiscountDescription()) {
                $discountLabel = __('%1', $orderData->getDiscountDescription());
            } else {
                $discountLabel = __('Discount');
            }
            $totals['discount'] = new \Magento\Framework\DataObject(
                [
                'code' => 'discount',
                'value' => $orderData->getDiscountAmount(),
                'label' => $discountLabel
                ]
            );
        }
        $totals['tax'] = new \Magento\Framework\DataObject(
            [
              'code' => 'tax',
              'value' => $orderData->getTaxAmount() ,
              'label' => __('Tax')
            ]
        );
        $totals['shipping'] = new \Magento\Framework\DataObject(
            [
              'code' => 'shipping',
              'value' => ceil($orderData->getShippingAmount()) == 0 ? 'FREE' : $orderData->getShippingAmount() ,
              'label' => __('Shipping')
            ]
        );
        $totals['grand_total'] = new \Magento\Framework\DataObject(
            [
              'code' => 'grand_total',
              'value' => $orderData->getGrandTotal(),
              'label' => __('Order Total')
            ]
        );
        return $totals;
    }

    public function formatValue($value)
    {
        return $value == 'FREE' ? $value : $this->getOrderData()->formatPrice($value);
    }

    public function getOrderItems()
    {
        $items = [];
        foreach ($this->getOrderData()->getAllItems() as $item) {
            $product = $this->productRepositoryFactory->create()->getById($item->getProductId());
            $productCollection = $this->getProductCollection($item->getProductId());
            $productForImage = $productCollection->getFirstItem();
            $thumbnailUrl = $this->productHelper->getThumbnailUrl($productForImage);
            $items[] = new \Magento\Framework\DataObject(
                [
                'name' => $product->getName(),
                'image' => $thumbnailUrl,
                'size_weight' => $product->getData('size_or_weight'),
                'price' => $item->getPrice(),
                'qty' => ceil($item->getQtyOrdered()),
                'total' => $item->getRowtotal()
                ]
            );
        }
        return $items;
    }

    public function getProductCollection($productId)
    {
        $collection = $this->productCollectionFactory->create()->addIdFilter($productId, false);
        $collection->addAttributeToSelect('*');
        return $collection;
    }

    public function getCustomerSubscription($customerId)
    {
        $profileCollectionFactory = $this->profileCollectionFactory->create();
        $profileCollectionFactory->addFieldToFilter('customer_id', ['eq' => $customerId]);
        return $profileCollectionFactory->getSize();
    }

    /**
     * Check customer is ssm or not in new similac
     * @return boolean
     */
    public function isSSM()
    {
        $orderData = $this->getOrderData();
        $customer = $this->customerRepositoryInterface->getById($orderData->getCustomerId());
        $isSSM = $customer->getCustomAttribute('user_type');
        if($isSSM && strtolower(trim($isSSM->getValue())) == 'similac-ssm'){
            return true;
        } else {
            return false;
        }
    }

    /**
     * To return all the sku's in order placed
     * @return object
     */

    public function getOrderedItems()
    {
        $items = array();
        foreach ($this->getOrderData()->getAllItems() as $item) {
            $items['all_sku'][] = $item->getSku();

        }
        return $this->_json->serialize($items);
    }

     /**
     * To return do tag value
     * @return array
     */
    public function getDoTagValue(){
        $value = [];
        $value['do_tag'] = $this->dataHelper->getDoTagValue();
        $value['source_url'] = $this->dataHelper->getSourceUrl();
        $value['config'] = $this->dataHelper->getModuleConfig();
        return $this->_json->serialize($value);
    }

}
