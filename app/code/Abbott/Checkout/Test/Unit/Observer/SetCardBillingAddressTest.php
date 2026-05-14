<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Abbott\Checkout\Test\Unit\Observer;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\OrderFactory;
use Abbott\Checkout\Observer\SetCardBillingAddress;
use Magento\Vault\Model\ResourceModel\PaymentToken;
use Magento\Customer\Api\AddressRepositoryInterface;
use Abbott\CreditCards\Model\AddressPaymentTokenLinkFactory;
use Psr\Log\LoggerInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Abbott\CreditCards\Model\AddressPaymentTokenLink;

/**
 * Test for \Aheadworks\Sarp2\Observer\AssignProfileToCustomerObserver
 */
class SetCardBillingAddressTest extends TestCase
{

    public $orderFactoryMock;
    /**
     * @var (\PHPUnit\Framework\MockObject\MockObject & \Psr\Log\LoggerInterface)
     */
    public $logger;
    public $objSetCardBillingAddress;
    /**
     * @var AssignProfileToCustomerObserver
     */
    private $observerMock;
    private $paymentTokenMock;
    private $addressRepositoryInterfaceMock;
    private $addressPaymentTokenLinkFactoryMock;
    private $paymentTokenManagementInterfaceMock;
    private $quoteMock;
    private $eventMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->orderFactoryMock = $this->createMock(OrderFactory::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->paymentTokenMock = $this->createMock(PaymentToken::class);
        $this->addressRepositoryInterfaceMock = $this->createMock(AddressRepositoryInterface::class);
        $this->addressPaymentTokenLinkFactoryMock = $this->createMock(AddressPaymentTokenLinkFactory::class);

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->paymentTokenManagementInterfaceMock = $this->createMock(PaymentTokenManagementInterface::class);
        $this->quoteMock = $this->createMock(QuoteFactory::class);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMock();

        $this->objSetCardBillingAddress = new SetCardBillingAddress(
            $this->paymentTokenMock,
            $this->addressRepositoryInterfaceMock,
            $this->addressPaymentTokenLinkFactoryMock,
            $this->logger,
            $this->paymentTokenManagementInterfaceMock,
            $this->quoteMock
        );
    }

    public function testExecute()
    {


        $orderId = '555349';

        $quoteId = '2464936';

        $customerId = '283098';

        $publickHash = '8bf11214dbe08ac74979a56fb0d207d5f606a835995008f74367244ed3f593ae';

        $tokenId = '19119';

        $addressId = '211374';

        $addressarray =  ['Firstname' => 'Test',
                          'Middlename' => 'Test',
                          'Lastname' => 'Test',
                          'Suffix' => '',
                          'Prefix' => '',
                          'Region' => ['Region' => 'Test'],
                          'RegionId' => '23',
                          'Street' => 'Test',
                          'City' => 'Test',
                          'Telephone' => '123456',
                          'Postcode' => '12345',
                          'CountryId' => 'US',
                          'Id' => $addressId
                         ];

        $orderMock =$this->createMock(\Magento\Sales\Model\Order::class);

        $paymentMock = $this->createMock(\Magento\Sales\Model\Order\Payment::class);

        $paymentTokenObj = $this->createMock(\Magento\Vault\Model\PaymentToken::class);

        $addressPaymentTokenLink = $this->createMock(AddressPaymentTokenLink::class);

        $address = $this->createMock(\Magento\Customer\Model\Data\Address::class);

        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);

        $region = $this->createMock(\Magento\Customer\Model\Data\Region::class);

        $quoteAddress = $this->createMock(\Magento\Quote\Model\Quote\Address::class);

        $orderAddress = $this->createMock(\Magento\Sales\Model\Order\Address::class);

        $this->orderFactoryMock->expects($this->any())->method('create')->willReturn($orderMock);

        $orderMock->expects($this->any())->method('load')->with($orderId)->willReturnSelf();

        $orderMock->expects($this->any())->method('getQuoteId')->willReturn($quoteId);

        $paymentMock->expects($this->any())->method('getMethod')->willReturn('braintree_cc_vault');

        $paymentMock->expects($this->any())->method('getAdditionalInformation')
                    ->willReturn(['public_hash' => $publickHash, 'customer_id' => $customerId]);

        $this->paymentTokenManagementInterfaceMock->expects($this->any())->method('getByPublicHash')
             ->with($publickHash, $customerId)->willReturn($paymentTokenObj);

        $paymentTokenObj->expects($this->any())->method('getEntityId')->willReturn($tokenId);

        $this->addressPaymentTokenLinkFactoryMock->expects($this->any())->method('create')
             ->willReturn($addressPaymentTokenLink);

        $addressPaymentTokenLink->expects($this->any())->method('getAddressIdByPaymentId')
                                ->willReturn($addressId);

        $this->addressRepositoryInterfaceMock->expects($this->any())->method('getById')
             ->with($addressId)->willReturn($address);

        $this->quoteMock->expects($this->any())->method('create')->willReturn($quote);

        $quote->expects($this->any())->method('load')->with($quoteId)->willReturnSelf();

        $address->expects($this->any())->method('getFirstname')->willReturn($addressarray['Firstname']);

        $address->expects($this->any())->method('getMiddlename')->willReturn($addressarray['Middlename']);

        $address->expects($this->any())->method('getLastname')->willReturn($addressarray['Lastname']);

        $address->expects($this->any())->method('getSuffix')->willReturn($addressarray['Suffix']);

        $address->expects($this->any())->method('getPrefix')->willReturn($addressarray['Prefix']);

        $address->expects($this->any())->method('getRegion')->willReturn($region);

        $region->expects($this->any())->method('getRegion')->willReturn($addressarray['Region']['Region']);

        $address->expects($this->any())->method('getRegionId')->willReturn($addressarray['RegionId']);

        $address->expects($this->any())->method('getStreet')->willReturn($addressarray['Street']);

        $address->expects($this->any())->method('getCity')->willReturn($addressarray['City']);

        $address->expects($this->any())->method('getTelephone')->willReturn($addressarray['Telephone']);

        $address->expects($this->any())->method('getPostcode')->willReturn($addressarray['Postcode']);

        $address->expects($this->any())->method('getCountryId')->willReturn($addressarray['CountryId']);

        $address->expects($this->any())->method('getId')->willReturn($addressId);

        $quote->expects($this->any())->method('getBillingAddress')->willReturn($quoteAddress);

        $quoteAddress->expects($this->any())->method('setFirstname')->willReturn($addressarray['Firstname']);

        $quoteAddress->expects($this->any())->method('setMiddlename')->willReturn($addressarray['Middlename']);

        $quoteAddress->expects($this->any())->method('setLastname')->willReturn($addressarray['Lastname']);

        $quoteAddress->expects($this->any())->method('setSuffix')->willReturn($addressarray['Suffix']);

        $quoteAddress->expects($this->any())->method('setPrefix')->willReturn($addressarray['Prefix']);

        $quoteAddress->expects($this->any())->method('setRegion')->willReturn($addressarray['Region']['Region']);

        $quoteAddress->expects($this->any())->method('setRegionId')->willReturn($addressarray['RegionId']);

        $quoteAddress->expects($this->any())->method('setStreet')->willReturn($addressarray['Street']);

        $quoteAddress->expects($this->any())->method('setCity')->willReturn($addressarray['City']);

        $quoteAddress->expects($this->any())->method('setTelephone')->willReturn($addressarray['Telephone']);

        $quoteAddress->expects($this->any())->method('setPostcode')->willReturn($addressarray['Postcode']);

        $quoteAddress->expects($this->any())->method('setCountryId')->willReturn($addressarray['CountryId']);

        $quoteAddress->expects($this->any())->method('setCustomerAddressId')->willReturn($addressId);

        $quoteAddress->expects($this->any())->method('save');

        $orderMock->expects($this->any())->method('getBillingAddress')->willReturn($orderAddress);

        $orderAddress->expects($this->any())->method('setFirstname')->willReturn($addressarray['Firstname']);

        $orderAddress->expects($this->any())->method('setMiddlename')->willReturn($addressarray['Middlename']);

        $orderAddress->expects($this->any())->method('setLastname')->willReturn($addressarray['Lastname']);

        $orderAddress->expects($this->any())->method('setSuffix')->willReturn($addressarray['Suffix']);

        $orderAddress->expects($this->any())->method('setPrefix')->willReturn($addressarray['Prefix']);

        $orderAddress->expects($this->any())->method('setRegion')->willReturn($addressarray['Region']['Region']);

        $orderAddress->expects($this->any())->method('setRegionId')->willReturn($addressarray['RegionId']);

        $orderAddress->expects($this->any())->method('setStreet')->willReturn($addressarray['Street']);

        $orderAddress->expects($this->any())->method('setCity')->willReturn($addressarray['City']);

        $orderAddress->expects($this->any())->method('setTelephone')->willReturn($addressarray['Telephone']);

        $orderAddress->expects($this->any())->method('setPostcode')->willReturn($addressarray['Postcode']);

        $orderAddress->expects($this->any())->method('setCountryId')->willReturn($addressarray['CountryId']);

        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($this->eventMock);

        $this->eventMock->expects($this->any())->method('getOrder')->willReturn($orderMock);

        $orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);

        $this->assertNull($this->objSetCardBillingAddress->execute($this->observerMock));
    }
}
