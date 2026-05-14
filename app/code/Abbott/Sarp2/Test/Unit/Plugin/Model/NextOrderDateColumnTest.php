<?php

namespace Abbott\DashboardReport\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Abbott\Sarp2\Plugin\NextOrderDateColumn as NextOrderDateColumn;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Grid\Collection as SubscriptionProfileGridCollection;
use Aheadworks\Sarp2\Model\Profile as Profile;

/**
 * Class NextOrderDateColumnTest
 * @package Abbott\DashboardReport\Test\Unit\Model
 */
class NextOrderDateColumnTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NextOrderDateColumn
     */
    protected $nextOrderDateColumn;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var SubscriptionProfileGridCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subscriptionProfileGridCollection;

    /**
     * @var Profile|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $profile;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        /* Mock Subscription Profile Collection Data */
        $this->profile = $this->createMock(Profile::class);
        $this->subscriptionProfileGridCollection = $this->createPartialMock(
            SubscriptionProfileGridCollection::class, ['create']);

        /* Initialize Subsccription Profile objects */
        $this->subscriptionProfileGridCollection->expects($this->any())->method('create')
            ->will($this->returnValue($this->profile));

        /* Initialize custom NextOrderColumnDate class object */
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->nextOrderDateColumn = $this->objectManagerHelper->getObject(
            NextOrderDateColumn::class,
            [
                'subscriptionProfileGridCollection' => $this->subscriptionProfileGridCollection
            ]
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testAroundGetReport($subject, $result): void
    {
        /** @var SubscriptionProfileGridCollection $orderCollection */
        $subscriptionProfileCollection = $this->createMock(
            SubscriptionProfileGridCollection::class);

        $this->profile->expects($this->once())->method('getCollection')
            ->will($this->returnValue($subscriptionProfileCollection));

        $subscriptionProfileCollection->expects($this->once())->method('getSelect')
            ->will($this->returnSelf());
        $subscriptionProfileCollection->expects($this->once())->method('joinLeft')->will($this->returnSelf());

        $this->assertEquals($subject, $result);
    }
}
