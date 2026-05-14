<?php


namespace Abbott\Hartehanks\Test\Integration\Model;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Form\FormKey;
use Magento\Quote\Model\QuoteManagement;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\Service\OrderService;
use PHPUnit\Framework\TestCase;
use Abbott\Hartehanks\Model\HartehankPlaceOrderSync;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\Checkout\Model\Cart;

class TNSamplingTest extends TestCase
{
    public $orderFactory;
    /**
     * @var null
     */
    public $hartehankPlaceOrderSyn;
    protected $objectManager;
    protected $hartehankPlaceOrderSync;
    protected $_storeManager;
    protected $_product;
    protected $_formkey;
    protected $quoteManagement;
    protected $customerFactory;
    protected $customerRepository;
    protected $orderService;
    protected $product;
    protected $quote;
    protected $rate;
    protected $salesRule;
    protected $found;
    protected $cart;

    CONST Literature_SKU = "REP-CPN-1234";

    /**
     *
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();;
        $this->hartehankPlaceOrderSync = $this->objectManager->create(HartehankPlaceOrderSync::class);
        $this->_storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $this->_product = $this->objectManager->create(Product::class);
        $this->_formkey = $this->objectManager->create(FormKey::class);
        $this->quoteManagement = $this->objectManager->create(QuoteManagement::class);
        $this->customerFactory = $this->objectManager->create(CustomerFactory::class);
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->orderService = $this->objectManager->create(OrderService::class);
        $this->orderFactory = $this->objectManager->create(CollectionFactory::class);
        $this->quote = $this->objectManager->create(QuoteFactory::class);
        $this->rate = $this->objectManager->create(Rate::class);
        $this->salesRule = $this->objectManager->create(Rule::class);
        $this->found = $this->objectManager->create(Found::class);
        $this->cart = $this->objectManager->create(Cart::class);
    }

    /**
     * Destroy Object
     */
    protected function tearDown()
    {
        $this->objectManager = null;
        $this->hartehankPlaceOrderSyn = null;
        $this->_storeManager = null;
        $this->_product = null;
        $this->_formkey = null;
        $this->quoteManagement = null;
        $this->customerFactory = null;
        $this->customerRepository = null;
        $this->orderService = null;
        $this->orderFactory = null;
        $this->quote = null;
        $this->rate = null;
        $this->salesRule = null;
        $this->found = null;
        $this->cart = null;
    }

    public function testplaceOrder(){
        $coupon_code = $this->generateCoupon();
        $data = $this->orderData($coupon_code);
        $coupon_code = $this->createSalesRule($coupon_code);
        $orderId = $this->createMageOrder($data);
        $hh = $this->hartehankPlaceOrderSync->executeWithoutLimit($orderId);

        $HH_order = $hh['Place-Order-Service']['_value']['Orders']['_value']['Order']['_value']['OrderItems']['OrderItem'];

        foreach ($HH_order as $item){
            if($item['_attribute']['ProductCode'] == self::Literature_SKU){
                $this->assertEquals(
                    'REP-CPN-1234',
                    $item['_attribute']['ProductCode']
                );
                break;
            }
        }
    }

    public function generateEmail(){
        return time() . "@mailinator.com";
    }

    public function generateCoupon(){
        return time();
    }

    public function createSalesRule($coupon_code){
        $price = 100;
        $sku = '24-WG085';
        $discount = ($price - (($price + 1) / 2));

        $this->salesRule->setName('TN Sampling - ' . $sku)
            ->setDescription('Buy one item at regular price, and receive a second item for just $1.00 more!')
            ->setFromDate('2000-01-01')
            ->setToDate(NULL)
            ->setUsesPerCustomer('0')
            ->setCustomerGroupIds(array('0','1','2','3',))
            ->setIsActive('1')
            ->setStopRulesProcessing('0')
            ->setIsAdvanced('1')
            ->setProductIds(NULL)
            ->setSortOrder('1')
            ->setSimpleAction('by_percent')
            ->setDiscountAmount(100)
            ->setDiscountQty(NULL)
            ->setDiscountStep('0')
            ->setSimpleFreeShipping('0')
            ->setApplyToShipping('0')
            ->setTimesUsed('0')
            ->setIsRss('0')
            ->setWebsiteIds(array('1',))
            ->setCouponType(2)
            ->setCouponCode($coupon_code)
            ->setUsesPerCoupon(NULL)
            ->setSamplingStatus(1)
            ->setLiteratureSku(self::Literature_SKU);

        $item_found = $this->found
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
            ->setValue(1) // 1 == FOUND
            ->setAggregator('all'); // match ALL conditions

        $this->salesRule->getConditions()->addCondition($item_found);
        $this->salesRule->save();
    }

    public function getProductDetails(){
       /* $orders = $this->orderFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', 'complete');

        foreach ($orders->getFirstItem()->getAllVisibleItems() as $item){
            $this->product = $item->getProductId();
            //break;
        }*/

        return $this->_product->load(253);
    }

    public function orderData($coupon_code){

        return [
            'currency_id'  => 'USD',
            'email'        => $this->generateEmail(),
            'shipping_address' =>[
                'firstname'    => 'John', //address Details
                'lastname'     => 'Doe',
                'street' => '123 Demo',
                'city' => 'Chicago',
                'country_id' => 'US',
                'region' => 'California',
                'region_id' => 12,
                'postcode' => '94560',
                'telephone' => '0123456789',
                'fax' => '32423',
                'save_in_address_book' => 1
            ],
            'items'=> [ //array of product which order you want to create
                ['product_id'=>$this->product,'qty'=>1,'price'=>50],
            ],
            'coupon_code' =>  $coupon_code
        ];
    }

    /**
     * Create Order On Your Store
     *
     * @param array $orderData
     * @return array
     *
     */
    public function createMageOrder($orderData) {
        $store=$this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);// load customet by email address

        if(!$customer->getEntityId()){
            //If not avilable then create this customer
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData['shipping_address']['firstname'])
                ->setLastname($orderData['shipping_address']['lastname'])
                ->setEmail($orderData['email'])
                ->setPassword($orderData['email']);
            $customer->save();
        }
        $quote=$this->quote->create(); //Create object of quote
        $quote->setStore($store); //set store for which you create quote
        // if you have allready buyer id then you can load customer directly
        $customer= $this->customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer

        //add items in quote
        foreach($orderData['items'] as $item){
            $product=$this->getProductDetails();

            $product->setPrice(0);
            $quote->addProduct(
                $product,
                intval($item['qty'])
            );
        }

        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['shipping_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method

        $this->rate
            ->setCode('fedex_FEDEX_GROUND')
            ->getPrice(1);

        $shippingAddress=$quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('fedex_FEDEX_GROUND'); //shipping method

        $quote->getShippingAddress()->addShippingRate($this->rate);

        $quote->setPaymentMethod('free'); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory

        //$quote->setCouponCode(self::COUPON_CODE)->collectTotals();
        $quote->save(); //Now Save quote and your quote is ready

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => 'free']);
        $quote->setCouponCode($orderData['coupon_code']);
        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);

        $order->setEmailSent(0);
        $order->setStatus('processing')->save();
        $increment_id = $order->getRealOrderId();
        if($order->getEntityId()){
            return $order->getRealOrderId();
        }else{
            $result=['error'=>1,'msg'=>'Your custom message'];
        }
        return $result;
    }
}
