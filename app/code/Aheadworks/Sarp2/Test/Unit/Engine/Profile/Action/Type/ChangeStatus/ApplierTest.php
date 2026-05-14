<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Engine\Profile\Action\Type\ChangeStatus;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Applier;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\StatusMap;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\Result;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Applier
 */
class ApplierTest extends \PHPUnit\Framework\TestCase
{
    const CALL = '__call';
    /**
     * @var Applier
     */
    private $applier;

    /**
     * @var StatusMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusMapMock;

    /**
     * @var Status|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusSourceMock;

    /**
     * @var PaymentsList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentsListMock;

    /**
     * @var Persistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentPersistenceMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResultFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->statusMapMock = $this->createMock(StatusMap::class);
        $this->statusSourceMock = $this->createMock(Status::class);
        $this->paymentsListMock = $this->createMock(PaymentsList::class);
        $this->paymentPersistenceMock = $this->createMock(Persistence::class);
        $this->validationResultFactoryMock = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->applier = $objectManager->getObject(
            Applier::class,
            [
                'statusMap' => $this->statusMapMock,
                'statusSource' => $this->statusSourceMock,
                'paymentsList' => $this->paymentsListMock,
                'paymentPersistence' => $this->paymentPersistenceMock,
                'validationResultFactory' => $this->validationResultFactoryMock
            ]
        );
    }

    /**
     * @param string $status
     * @param bool $isReactivated
     * @dataProvider applyDataProvider
     */
    public function testApply($status, $isReactivated)
    {
        $profileId = 1;

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);
        /** @var ActionInterface|\PHPUnit_Framework_MockObject_MockObject $actionMock */
        $actionMock = $this->createMock(ActionInterface::class);
        $actionDataMock = $this->createMock(DataObject::class);
        $paymentMock = $this->createMock(PaymentInterface::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);

        $actionMock->expects($this->once())
            ->method('getData')
            ->willReturn($actionDataMock);
        $actionDataMock->expects($this->once())
            ->method(self::CALL)
            ->with('getStatus')
            ->willReturn($status);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $this->paymentsListMock->expects($this->once())
            ->method('getLastScheduled')
            ->with($profileId)
            ->willReturn([$paymentMock]);
        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $scheduleMock->expects($this->once())
            ->method('setIsReactivated')
            ->with($isReactivated);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with($status);
        $this->paymentPersistenceMock->expects($this->once())
            ->method('massSave')
            ->with([$paymentMock]);

        $this->applier->apply($profileMock, $actionMock);
    }

    public function testApplyNoPayments()
    {
        $profileId = 1;
        $status = Status::SUSPENDED;

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);
        /** @var ActionInterface|\PHPUnit_Framework_MockObject_MockObject $actionMock */
        $actionMock = $this->createMock(ActionInterface::class);
        $actionDataMock = $this->createMock(DataObject::class);

        $actionMock->expects($this->once())
            ->method('getData')
            ->willReturn($actionDataMock);
        $actionDataMock->expects($this->once())
            ->method(self::CALL)
            ->with('getStatus')
            ->willReturn($status);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $this->paymentsListMock->expects($this->once())
            ->method('getLastScheduled')
            ->with($profileId)
            ->willReturn([]);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with($status);
        $this->paymentPersistenceMock->expects($this->never())
            ->method('massSave');

        $this->applier->apply($profileMock, $actionMock);
    }

    /**
     * @param string $status
     * @param string $origStatus
     * @param array $allowedStatuses
     * @param array $statusOptions
     * @param array $expectedResultData
     * @dataProvider validateDataProvider
     */
    public function testValidate(
        $status,
        $origStatus,
        $allowedStatuses,
        $statusOptions,
        $expectedResultData
    ) {
        /** @var ProfileInterface|Profile|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(Profile::class);
        /** @var ActionInterface|\PHPUnit_Framework_MockObject_MockObject $actionMock */
        $actionMock = $this->createMock(ActionInterface::class);
        $actionDataMock = $this->createMock(DataObject::class);
        $validationResultMock = $this->createMock(Result::class);

        $actionMock->expects($this->once())
            ->method('getData')
            ->willReturn($actionDataMock);
        $actionDataMock->expects($this->once())
            ->method(self::CALL)
            ->with('getStatus')
            ->willReturn($status);
        $profileMock->expects($this->once())
            ->method('getOrigData')
            ->with('status')
            ->willReturn($origStatus);
        $profileMock->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn($origStatus);
        $this->statusMapMock->expects($this->once())
            ->method('getAllowedStatuses')
            ->with($origStatus)
            ->willReturn($allowedStatuses);
        $this->statusSourceMock->expects($this->any())
            ->method('getOptions')
            ->willReturn($statusOptions);
        $this->validationResultFactoryMock->expects($this->once())
            ->method('create')
            ->with($expectedResultData)
            ->willReturn($validationResultMock);

        $this->assertSame($validationResultMock, $this->applier->validate($profileMock, $actionMock));
    }

    /**
     * @param PaymentInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $paymentMocks
     * @param array $expectedResultData
     * @dataProvider validateReactivationDataProvider
     */
    public function testValidateReactivation($paymentMocks, $expectedResultData)
    {
        $profileId = 1;
        $status = Status::ACTIVE;
        $origStatus = Status::SUSPENDED;

        /** @var ProfileInterface|Profile|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(Profile::class);
        /** @var ActionInterface|\PHPUnit_Framework_MockObject_MockObject $actionMock */
        $actionMock = $this->createMock(ActionInterface::class);
        $actionDataMock = $this->createMock(DataObject::class);
        $validationResultMock = $this->createMock(Result::class);

        $actionMock->expects($this->once())
            ->method('getData')
            ->willReturn($actionDataMock);
        $actionDataMock->expects($this->once())
            ->method(self::CALL)
            ->with('getStatus')
            ->willReturn($status);
        $profileMock->expects($this->once())
            ->method('getOrigData')
            ->with('status')
            ->willReturn($origStatus);
        $profileMock->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn($origStatus);
        $this->statusMapMock->expects($this->once())
            ->method('getAllowedStatuses')
            ->with($origStatus)
            ->willReturn([Status::ACTIVE]);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $this->paymentsListMock->expects($this->once())
            ->method('getLastScheduled')
            ->with($profileId)
            ->willReturn($paymentMocks);
        $this->statusSourceMock->expects($this->any())
            ->method('getOptions')
            ->willReturn([Status::ACTIVE => 'Active']);
        $this->validationResultFactoryMock->expects($this->once())
            ->method('create')
            ->with($expectedResultData)
            ->willReturn($validationResultMock);

        $this->assertSame($validationResultMock, $this->applier->validate($profileMock, $actionMock));
    }

    /**
     * @return array
     */
    public function applyDataProvider()
    {
        return [
            [Status::SUSPENDED, false],
            [Status::ACTIVE, true]
        ];
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                Status::SUSPENDED,
                Status::ACTIVE,
                [Status::SUSPENDED],
                [],
                ['isValid' => true]
            ],
            [
                Status::ACTIVE,
                Status::ACTIVE,
                [Status::SUSPENDED],
                [],
                ['isValid' => true]
            ],
            [
                Status::EXPIRED,
                Status::ACTIVE,
                [Status::SUSPENDED],
                [Status::EXPIRED => 'Expired'],
                ['isValid' => false, 'message' => 'Profile status Expired is not allowed.']
            ]
        ];
    }

    /**
     * @return array
     */
    public function validateReactivationDataProvider()
    {
        return [
            [[], ['isValid' => true]],
            [
                [
                    $this->createConfiguredMock(
                        PaymentInterface::class,
                        ['getType' => PaymentInterface::TYPE_PLANNED]
                    )
                ],
                ['isValid' => true]
            ],
            [
                [
                    $this->createConfiguredMock(
                        PaymentInterface::class,
                        ['getType' => PaymentInterface::TYPE_REATTEMPT]
                    )
                ],
                [
                    'isValid' => false,
                    'message' => 'Unable to perform activation action, subscription suspended due to payment failures.'
                ]
            ]
        ];
    }
}
