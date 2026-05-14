<?php

namespace Abbott\TaxRefund\Test\Unit;

class RefundTest extends \PHPUnit\Framework\TestCase
{
    public $block;
    const GETINVOICECOLLECTION = 'getInvoiceCollection';
    const GETSTATE = 'getState';
    const GETALLITEMS = 'getAllItems';
    const GETENTITYID = 'getEntityId';

    /**
     * @return void
     */
    public function setUp()
    {
        $this->block = $this->getMockBuilder(\Abbott\TaxRefund\Console\Command\Refund::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return void
     */
    public function testGetPaidInvoiceForOrderPositive()
    {
        $testMethod = new \ReflectionMethod(\Abbott\TaxRefund\Console\Command\Refund::class, 'getPaidInvoiceForOrder');
        $testMethod->setAccessible(true);
        $orderMock = $this->createPartialMock(\Magento\Sales\Model\Order::class, [self::GETINVOICECOLLECTION]);
        $invoices = [];
        $previousInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)->disableOriginalConstructor()->setMethods([self::GETSTATE])->getMock();
        $previousInvoice->expects($this->any())->method(self::GETSTATE)->will($this->returnValue(2));
        $invoices[] = $previousInvoice;
        $orderMock->expects($this->any())->method(self::GETINVOICECOLLECTION)->will($this->returnValue($invoices));
        $this->assertInstanceOf(\Magento\Sales\Model\Order\Invoice::class, $testMethod->invokeArgs($this->block, [$orderMock]));
    }

    /**
     * @return void
     */
    public function testGetGivenInvoiceForOrderPositive()
    {
        $testMethod = new \ReflectionMethod(\Abbott\TaxRefund\Console\Command\Refund::class, 'getGivenInvoiceForOrder');
        $testMethod->setAccessible(true);
        $orderMock = $this->createPartialMock(\Magento\Sales\Model\Order::class, [self::GETINVOICECOLLECTION]);
        $invoices = [];
        $previousInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)->disableOriginalConstructor()->setMethods([self::GETSTATE,self::GETENTITYID])->getMock();
        $previousInvoice->expects($this->any())->method(self::GETSTATE)->will($this->returnValue(2));
        $previousInvoice->expects($this->any())->method(self::GETENTITYID)->will($this->returnValue(1));
        $invoices[] = $previousInvoice;
        $orderMock->expects($this->any())->method(self::GETINVOICECOLLECTION)->will($this->returnValue($invoices));
        $this->assertInstanceOf(\Magento\Sales\Model\Order\Invoice::class, $testMethod->invokeArgs($this->block, [$orderMock,1]));
    }

    /**
     * @return void
     */
    public function testGetPaidInvoiceForOrderNegative()
    {
        $testMethod = new \ReflectionMethod(\Abbott\TaxRefund\Console\Command\Refund::class, 'getPaidInvoiceForOrder');
        $testMethod->setAccessible(true);
        $orderMock = $this->createPartialMock(\Magento\Sales\Model\Order::class, [self::GETINVOICECOLLECTION]);
        $invoices = [];
        $previousInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)->disableOriginalConstructor()->setMethods([self::GETSTATE])->getMock();
        $previousInvoice->expects($this->any())->method(self::GETSTATE)->will($this->returnValue(1));
        $invoices[] = $previousInvoice;
        $orderMock->expects($this->any())->method(self::GETINVOICECOLLECTION)->will($this->returnValue($invoices));
        $this->assertEmpty($testMethod->invokeArgs($this->block, [$orderMock]));
    }

    /**
     * @return void
     */
    public function testGetGivenInvoiceForOrderNegative()
    {
        $testMethod = new \ReflectionMethod(\Abbott\TaxRefund\Console\Command\Refund::class, 'getGivenInvoiceForOrder');
        $testMethod->setAccessible(true);
        $orderMock = $this->createPartialMock(\Magento\Sales\Model\Order::class, [self::GETINVOICECOLLECTION]);
        $invoices = [];
        $previousInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)->disableOriginalConstructor()->setMethods([self::GETSTATE,self::GETENTITYID])->getMock();
        $previousInvoice->expects($this->any())->method(self::GETSTATE)->will($this->returnValue(1));
        $previousInvoice->expects($this->any())->method(self::GETENTITYID)->will($this->returnValue(1));
        $invoices[] = $previousInvoice;
        $orderMock->expects($this->any())->method(self::GETINVOICECOLLECTION)->will($this->returnValue($invoices));
        $this->assertEmpty($testMethod->invokeArgs($this->block, [$orderMock,2]));
    }

    /**
     * @return void
     */
    public function testGetItemsArrayNegative()
    {
        $testMethod = new \ReflectionMethod(\Abbott\TaxRefund\Console\Command\Refund::class, 'getItemsArray');
        $testMethod->setAccessible(true);
        $invMock = $this->createPartialMock(\Magento\Sales\Model\Order\Invoice::class, [self::GETALLITEMS]);
        $items = [];
        $invMock->expects($this->any())->method(self::GETALLITEMS)->will($this->returnValue($items));
        $this->assertEmpty($testMethod->invokeArgs($this->block, [$invMock]));
    }



    /**
     * @return void
     */
    public function testGetItemsArrayPositive()
    {
        $testMethod = new \ReflectionMethod(\Abbott\TaxRefund\Console\Command\Refund::class, 'getItemsArray');
        $testMethod->setAccessible(true);
        $invMock = $this->createPartialMock(\Magento\Sales\Model\Order\Invoice::class, [self::GETALLITEMS]);
        $items = [];
        $item = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice\Item::class)->disableOriginalConstructor()->setMethods(['getOrderItemId'])->getMock();
        $item->expects($this->any())->method('getOrderItemId')->will($this->returnValue(212));
        $items[] = $item;
        $invMock->expects($this->any())->method(self::GETALLITEMS)->will($this->returnValue($items));
        $this->assertEquals([212 => ["qty" => 0]], $testMethod->invokeArgs($this->block, [$invMock]));
    }
}
