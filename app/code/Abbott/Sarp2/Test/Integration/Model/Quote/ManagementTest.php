<?php
namespace Abbott\Sarp2\Test\Integration\Model\Quote;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Abbott\Sarp2\Model\Quote\Management;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class ManagementTest extends TestCase{

    /**
     * @var Management
     */
    private $management;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileInterface;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $order;

    /**
     * @var CartRepositoryInterface
     */
    private $quote;

    /**
     * Const ORDERID
     */
    CONST ORDERID = 5112;

    /**
     * Build Object
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->management =  $this->objectManager->create(Management::class);
        $this->profileInterface =  $this->objectManager->create(ProfileRepositoryInterface::class);
        $this->order =  $this->objectManager->create(OrderRepositoryInterface::class);
        $this->quote =  $this->objectManager->create(CartRepositoryInterface::class);
    }

    /**
     * Destroy Object
     */
    protected function tearDown()
    {
        $this->objectManager = null;
        $this->management = null;
        $this->profileInterface = null;
        $this->order = null;
        $this->quote = null;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateProfiles(){

        $order =  $this->order->get(self::ORDERID);
        $quote =  $this->quote->get($order->getQuoteId());

        $profiles = $this->management->createProfiles($quote,$order);

        foreach ($profiles as $profile){
            $profile = $this->profileInterface->get($profile);
            $this->assertEquals(self::ORDERID,$profile->getLastOrderId());
        }
    }
}
