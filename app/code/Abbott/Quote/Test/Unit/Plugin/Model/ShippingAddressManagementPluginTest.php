<?php
namespace Abbott\Quote\Test\Unit\Plugin\Model;

use Abbott\Quote\Plugin\Model\ShippingAddressManagementPlugin;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Data\Address;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\ShippingAddressManagement;

class ShippingAddressManagementPluginTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartRepositoryMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressRepositoryMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;
    /**
     * @var object
     */
    protected $shippingManagementPluginMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $billingAddressMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressMock;

    /**
     *
     */
    public function setUp() : void
    {
        $this->cartRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRepositoryMock = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressRepositoryMock = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getItems', 'setLastAddedItem', 'getBillingAddress', 'getExtensionAttributes', 'isVirtual',
                    'collectTotals', 'getCustomerId'
                ]
            )
            ->getMock();
        $this->billingAddressMock = $this->getMockBuilder(QuoteAddress::class)
            ->setMethods(['getCustomerAddress', 'getCustomerAddressId', 'setCustomerAddressId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressMock = $this->getMockBuilder(Address::class)
            ->setMethods(['getId', 'getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->shippingManagementPluginMock = $this->objectManagerHelper->getObject(
            ShippingAddressManagementPlugin::class,
            [
                'cartRepository' => $this->cartRepositoryMock,
                'addressRepository' => $this->addressRepositoryMock
            ]
        );
    }

    /**
     * Asserting that in case if address doesn't match, we will reset value to null and still proceed
     */
    public function testBeforeAssignWrongAddressCustomerId() : void
    {
        $cartId = 5;
        $customerAddressId = 10;
        $addressCustomerId = 15;
        $quoteCustomerId = 20;

        $this->cartRepositoryMock->expects(static::atLeastOnce())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->billingAddressMock->expects(static::atLeastOnce())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);

        $this->addressRepositoryMock->expects(static::once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($this->addressMock);
        $this->addressMock->expects(static::never())
            ->method('getId')
            ->willReturn($customerAddressId);
        $this->addressMock->expects(static::atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($addressCustomerId);
        $this->quoteMock->expects(static::atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($quoteCustomerId);
        $this->billingAddressMock->expects(static::atLeastOnce())
            ->method('setCustomerAddressId')
            ->willReturn(null);

        $this->assertNotEquals($this->addressMock->getCustomerId(), $this->quoteMock->getCustomerId());

        $shippingManagementMock = $this->getMockBuilder(ShippingAddressManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(
            [$cartId, $this->billingAddressMock],
            $this->shippingManagementPluginMock->beforeAssign(
                $shippingManagementMock,
                $cartId,
                $this->billingAddressMock
            )
        );
    }
}
