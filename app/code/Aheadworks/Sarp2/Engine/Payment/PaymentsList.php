<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\CollectionFactory;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class PaymentsList
 * @package Aheadworks\Sarp2\Engine\Payment
 */
class PaymentsList
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var IsProcessable
     */
    private $isProcessableChecker;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param CollectionFactory $collectionFactory
     * @param IsProcessable $isProcessableChecker
     * @param DateTime $dateTime
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        IsProcessable $isProcessableChecker,
        DateTime $dateTime
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->isProcessableChecker = $isProcessableChecker;
        $this->dateTime = $dateTime;
    }

    /**
     * Check if there are payments for specified profile
     *
     * @param int $profileId
     * @return bool
     */
    public function hasForProfile($profileId)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('profile_id', ['eq' => $profileId]);
        return $collection->getSize() > 0;
    }

    /**
     * Get processable payments for today
     *
     * @param string $type
     * @param int $storeId
     * @param int $tmzOffset
     * @param array|null $ids
     * @return Payment[]
     */
    public function getProcessablePaymentsForToday($type, $storeId, $tmzOffset, $ids = null)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'type',
            ['eq' => $type]
        )->addFieldToFilter(
            'payment_status',
            ['in' => $this->isProcessableChecker->getAvailablePaymentStatuses($type)]
        )->addFieldToFilter(
            'store_id',
            ['eq' => $storeId]
        )->addFieldToFilter(
            $type == PaymentInterface::TYPE_REATTEMPT
                ? 'retry_at'
                : 'scheduled_at',
            ['lteq' => $this->dateTime->formatDate($this->today($tmzOffset))]
        );

        if ($ids) {
            $collection->addFieldToFilter('item_id', ['in' => $ids]);
        }
        return $collection->getItems();
    }

    /**
     * Get last scheduled payment of profile
     *
     * @param int $profileId
     * @return Payment[]
     */
    public function getLastScheduled($profileId)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'profile_id',
            ['eq' => $profileId]
        )->addTypeStatusMapFilter(
            [
                PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PLANNED],
                PaymentInterface::TYPE_LAST_PERIOD_HOLDER => [PaymentInterface::STATUS_PLANNED],
                PaymentInterface::TYPE_ACTUAL => [PaymentInterface::STATUS_PENDING],
                PaymentInterface::TYPE_REATTEMPT => [
                    PaymentInterface::STATUS_PENDING,
                    PaymentInterface::STATUS_RETRYING
                ],
                PaymentInterface::TYPE_OUTSTANDING => [PaymentInterface::STATUS_OUTSTANDING]
            ]
        )->setOrder('scheduled_at', Collection::SORT_ORDER_ASC);
        return $collection->getItems();
    }

    /**
     * Get today date time used in filter condition
     *
     * @param int $offset
     * @return array
     */
    private function today($offset)
    {
        $today = new \DateTime();
        if ($offset != 0) {
            $intervalSpec = 'PT' . abs($offset) . 'S';
            if ($offset > 0) {
                $today->sub(new \DateInterval($intervalSpec));
            } else {
                $today->add(new \DateInterval($intervalSpec));
            }
        }
        $today = $this->dateTime->formatDate($today);
        return new \DateTime($today);
    }
}
