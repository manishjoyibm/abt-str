<?php

namespace Abbott\MyAccount\Test\Unit\Plugin\View\Element\Html;

class LinksPluginTest extends \PHPUnit\Framework\TestCase
{
    public $linkDataHelperMock;
    public $linkPluginMock;
    protected $hide = true;

    protected $ilnkMock;

    protected $proceed;

    public function setUp() : void
    {
         $this->linkDataHelperMock = $this->getMockBuilder(\Abbott\MyAccount\Helper\LinkData::class)->disableOriginalConstructor()->setMethods(array('isEnabled', 'getAction'))->getMock();
          $abstractBlockMock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)->disableOriginalConstructor()->getMock();
        $this->ilnkMock = $this->getMockBuilder(\Magento\Framework\View\Element\Html\Links::class)->disableOriginalConstructor()->getMock();
        $this->proceed = $this->getMockBuilder(Closure::class)->getMock();
        $this->linkPluginMock = $this->getMockBuilder(
			\Abbott\MyAccount\Plugin\View\Element\Html\LinksPlugin::class)
			->setConstructorArgs(
				[
					$this->linkDataHelperMock
				]
                                
			)
			->getMock(); 
    }
    
    /**
     * 
     * @dataProvider executeDataProvider
     */
     public function testAroundRenderLinkCase1($ilnkMock, $proceed, $abstractBlockMock) : void
    {
          $testMethod = new \ReflectionMethod(\Abbott\MyAccount\Plugin\View\Element\Html\LinksPlugin::class, 'aroundRenderLink');
          $testMethod->setAccessible(true);
         // $links = $link->getNameInLayout(); 
        $subject = $this->getMockBuilder(\Magento\Framework\View\Element\Html\Links::class)->disableOriginalConstructor()->getMock();
       
        $test = [
            'is_enabled' => 1,
            'action' => 1,
            'sections' => [
                'my_account',
                "downloadable_product",
                "store_credit",
                "gift_card"
            ],
            'expected_result' => true
        ];
          $isEnabled = 1;
          $action = 
          $this->linkDataHelperMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($test['isEnabled']);
           $this->linkDataHelperMock->expects($this->any())
            ->method('getAction')
            ->willReturn($test['action']);
         
           $testMethod->invokeArgs($this->linkPluginMock, [$subject]);
           $this->assertEquals($test['expected_result'], $this->hide, "Hide links from menu Enable");
          
     }
     
      /**
     * 
     * @dataProvider executeDataProvider
     */  
     public function testAroundRenderLinkCase2($ilnkMock, $proceed, $abstractBlockMock) : void
    {
            $output = $proceed($this->ilnkMock);
          $testMethod = new \ReflectionMethod(\Abbott\MyAccount\Plugin\View\Element\Html\LinksPlugin::class, 'aroundRenderLink');
          $testMethod->setAccessible(true);
         // $links = $link->getNameInLayout(); 
        $subject = $this->getMockBuilder(\Magento\Framework\View\Element\Html\Links::class)->disableOriginalConstructor()->getMock();
        $test = [
            'is_enabled' => 0,
            'action' => 1,
            'sections' => [
                'my_account',
                "downloadable_product",
                "store_credit",
                "gift_card"
            ],
            'expected_result' => false
        ];
          $isEnabled = 0;
          $action = 
          $this->linkDataHelperMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($test['isEnabled']);
           $this->linkDataHelperMock->expects($this->any())
            ->method('getAction')
            ->willReturn($test['action']);
           $this->linkDataHelperMock->expects($this->any())
            ->method('getSectionList')
            ->willReturn($test['sections']);
         
           $testMethod->invokeArgs($this->linkPluginMock, [$this->ilnkMock]);
           $this->assertEquals($test['expected_result'], $this->hide, "Hide links from menu Disable");
          
     }
     
    /**
     * Data provider for execute test
     * @return array
     */
    public function executeDataProvider()
    { 
        return [
            [$this->ilnkMock, $this->proceed, 'my_account'],
            [$this->ilnkMock, $this->proceed, 'downloadable_product'],
            [$this->ilnkMock, $this->proceed, 'store_credit'],
            [$this->ilnkMock, $this->proceed, 'gift_card']
        ];
    }
}