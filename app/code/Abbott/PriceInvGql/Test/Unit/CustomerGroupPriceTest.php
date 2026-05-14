<?php

namespace Abbott\PriceInvGql\Test\Unit;

class CustomerGroupPriceTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    public $customerFactoryMock;
    public $customerGroupMock;
    public $productRepoMock;
    public $productFactoryMock;
    /**
     * @var (\Aheadworks\Sarp2\Api\PlanRepositoryInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $planRepoMock;
    /**
     * @var (\Magento\Authorization\Model\UserContextInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $contextMock;
    /**
     * @var (\Magento\Customer\Model\Session & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $sessionMock;
    public $customerGroupPrice;
    /** 
     * @return void
    */
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerFactoryMock = $this->createMock(\Magento\Customer\Model\CustomerFactory::class);
        $this->customerGroupMock = $this->createMock(\Magento\Customer\Api\GroupRepositoryInterface::class);
        $this->productRepoMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productFactoryMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\ProductFactory::class);
        $this->planRepoMock  =  $this->createMock(\Aheadworks\Sarp2\Api\PlanRepositoryInterface::class);
        $this->contextMock  =  $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->sessionMock  =  $this->createMock(\Magento\Customer\Model\Session::class);

        $this->customerGroupPrice = $this->objectManager->getObject(
            \Abbott\PriceInvGql\Model\Product\Subscription\PriceCalculation::class,
            [
                'customerFactory' => $this->customerFactoryMock,
                'custGroupRepository' => $this->customerGroupMock,
                'productRepository' => $this->productRepoMock,
                'productFactory' => $this->productFactoryMock,
                'planRepository' => $this->planRepoMock,
                'customerSession' => $this->sessionMock,
                'context'  => $this->contextMock                
            ]
        );
    }

    /** 
     * @return void
    */
    public function testGetSubscriptionCustomerGroupPriceWithEmployee()
    {
        $productId = 565;
        $optionPrice = 10;
        $customerId = 283521;
        $customerGroupId = 2;
        $customerMock =$this->createMock(\Magento\Customer\Model\Customer::class);
        $this->customerFactoryMock->expects($this->once())
            ->method('create')->willReturn($customerMock);

        $customerMock->expects($this->once())->method('load')->with($customerId)->willReturnSelf();
        $customerMock->expects($this->once())->method('getGroupId')->willReturn($customerGroupId);

         $customerGroupDataMock = $this->createMock(\Magento\Customer\Model\Data\Group::class);

         $this->customerGroupMock->expects($this->once())
            ->method('getById')->with($customerGroupId)->willReturn($customerGroupDataMock);
         $customerGroupDataMock->expects($this->once())->method('getCode')->willReturn("Employee");


        $producMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productRepoMock->expects($this->once())->method('getById')->with($productId)->willReturn($producMock);
        $producMock->expects($this->once())->method('getPrice')->willReturn(50);

        $productResourceMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($productResourceMock);

        $eavAttrMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);        
        $productResourceMock->expects($this->once())->method('getAttribute')->with('subscribe_customer_group')->willReturn($eavAttrMock);
        $eavAttrMock->expects($this->once())->method('usesSource')->willReturn(1);

        $producMock->expects($this->once())->method('getData')->with("subscribe_customer_group")->willReturn(0);

        
        $attrSourceMock = $this->createMock(\Abbott\AdditionalAttributes\Model\Product\Attribute\Source\SubscribeCustomerGroup::class);   
        $eavAttrMock->expects($this->once())->method('getSource')->willReturn($attrSourceMock);
        $attrSourceMock->expects($this->once())->method('getOptionText')->willReturn("Consumer");
        $tierPriceMock  = $this->createMock(\Magento\Catalog\Model\Product\TierPrice::class);
        
        $tierPriceMock->expects($this->at(0))->method('getData')->with("customer_group_id")->willReturn($customerGroupId);

        $producMock->expects($this->once())->method('getTierPrices')->willReturn([$tierPriceMock]);

        $tierPriceMock->expects($this->at(1))->method('getData')->with("value")->willReturn(30);
        

        $finalPrice = $this->customerGroupPrice->getSubscriptionCustomerGroupPrice($productId, $optionPrice, $customerId);

        $this->assertEquals(30, $finalPrice);        
        
    }

    /** 
     * @return void
    */
    public function testGetSubscriptionCustomerGroupPriceWithConsumer()
    {
        $productId = 565;
        $optionPrice = 10;
        $customerId = 283521;
        $customerGroupId = 2;
        $customerMock =$this->createMock(\Magento\Customer\Model\Customer::class);
        $this->customerFactoryMock->expects($this->once())
            ->method('create')->willReturn($customerMock);

        $customerMock->expects($this->once())->method('load')->with($customerId)->willReturnSelf();
        $customerMock->expects($this->once())->method('getGroupId')->willReturn($customerGroupId);

         $customerGroupDataMock = $this->createMock(\Magento\Customer\Model\Data\Group::class);

         $this->customerGroupMock->expects($this->once())
            ->method('getById')->with($customerGroupId)->willReturn($customerGroupDataMock);
         $customerGroupDataMock->expects($this->once())->method('getCode')->willReturn("Consumer");


        $producMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productRepoMock->expects($this->once())->method('getById')->with($productId)->willReturn($producMock);
        $producMock->expects($this->once())->method('getPrice')->willReturn(50);

        $productResourceMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($productResourceMock);

        $eavAttrMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);        
        $productResourceMock->expects($this->once())->method('getAttribute')->with('subscribe_customer_group')->willReturn($eavAttrMock);
        $eavAttrMock->expects($this->once())->method('usesSource')->willReturn(1);

        $producMock->expects($this->once())->method('getData')->with("subscribe_customer_group")->willReturn(0);

        
        $attrSourceMock = $this->createMock(\Abbott\AdditionalAttributes\Model\Product\Attribute\Source\SubscribeCustomerGroup::class);   
        $eavAttrMock->expects($this->once())->method('getSource')->willReturn($attrSourceMock);

        $attrSourceMock->expects($this->once())->method('getOptionText')->willReturn("Consumer");        
        

        $finalPrice = $this->customerGroupPrice->getSubscriptionCustomerGroupPrice($productId, $optionPrice, $customerId);
        
        $this->assertEquals($optionPrice, $finalPrice);
        
    }
}
