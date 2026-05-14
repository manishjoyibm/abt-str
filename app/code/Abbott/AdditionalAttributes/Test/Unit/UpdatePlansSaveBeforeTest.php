<?php

namespace Abbott\AdditionalAttributes\Test\Unit;



class UpdatePlansSaveBeforeTest extends \PHPUnit\Framework\TestCase
{
    public $requestMock;
    /**
     * @var (\Magento\Catalog\Model\Product\Action & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $actionMock;
    public $productSaveAfterMock;
    /**
     * @var (\Magento\Catalog\Model\Product & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $productMock;
    public function setUp() : void
    {


        $this->requestMock  = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->disableOriginalConstructor()->getMock();


        $this->actionMock  = $this->getMockBuilder(\Magento\Catalog\Model\Product\Action::class)->disableOriginalConstructor()->getMock();

      

        $this->productSaveAfterMock = new \Abbott\AdditionalAttributes\Observer\UpdatePlansSaveBefore($this->requestMock, $this->actionMock);

    }

    public function testExecute()
    {
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $reqeustParams['product']['aw_sarp2_subscription_options'][] = ['plan_id'=>1,'website_id'=>5]; 
        $reqeustParams['product']['aw_sarp2_subscription_options'][] = ['plan_id'=>2,'website_id'=>0]; 
        $reqeustParams['store']=5;

        $this->requestMock->expects($this->any())
        ->method('getParams')
        ->will($this->returnValue($reqeustParams));

       
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getAttribute','getProduct','getResource']);

        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->any())->method('getProduct')->willReturn($eventMock);

        $resourceMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
        ->disableOriginalConstructor()
        ->setMethods(['getAttributeRawValue', 'duplicate', 'getAttribute'])
        ->getMock();

          $eventMock->expects($this->any())->method('getResource')->will($this->returnValue($resourceMock));

          $attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
        ->disableOriginalConstructor()
        ->getMock();

        $attribute->expects($this->any())
            ->method('usesSource')
            ->willReturn(1);

            $methods = ['getOptionId'];
            $optionOne = $this->createPartialMock(\Magento\Bundle\Model\Option::class, $methods);
            $optionOne->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

            $attribute->expects($this->any())
            ->method('getSource')
            ->willReturn($optionOne);

        $resourceMock->expects($this->any())
        ->method('getAttribute')->with('plans')
        ->willReturn($attribute);
        
        $this->productSaveAfterMock->execute($observerMock);

    } 

    public function testExecutetwo()
    {
        $reqeustParams['product']['aw_sarp2_subscription_options'] = [];
        $reqeustParams['store']=5;
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getAttribute','getProduct','getResource']);

        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->any())->method('getProduct')->willReturn($eventMock);
        $this->productSaveAfterMock->execute($observerMock);
    }

    
   
}
