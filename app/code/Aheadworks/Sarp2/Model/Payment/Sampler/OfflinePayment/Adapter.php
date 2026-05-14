<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\OfflinePayment;

use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInterface;

/**
 * Class Adapter
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\OfflinePayment
 */
class Adapter implements SamplerInterface
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * {@inheritdoc}
     */
    public function importPayment(SamplerInfoInterface $info, array $data)
    {
        return $info
            ->setAdditionalInformation('aw_sarp_payment_token_id', null)
            ->setAdditionalInformation('aw_sarp_skip_payment_token', true);
    }

    /**
     * {@inheritdoc}
     */
    public function place(SamplerInfoInterface $info)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function revert(SamplerInfoInterface $info)
    {
        return $this;
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return true;
    }

    /**
     * Check authorize availability
     *
     * @return bool
     */
    public function canAuthorize()
    {
        return true;
    }

    /**
     * Check void command availability
     *
     * @return bool
     */
    public function canVoid()
    {
        return true;
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return void
     */
    public function setStore($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStore()
    {
        return $this->storeId;
    }
}
