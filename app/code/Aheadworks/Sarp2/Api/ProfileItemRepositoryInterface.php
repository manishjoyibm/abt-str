<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Api;

/**
 * Interface ProfileItemRepositoryInterface
 * @package Aheadworks\Sarp2\Api
 */
interface ProfileItemRepositoryInterface
{
    /**
     * Save profile item
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileItemInterface $item
     * @return \Aheadworks\Sarp2\Api\Data\ProfileItemInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(\Aheadworks\Sarp2\Api\Data\ProfileItemInterface $item);

    /**
     * Retrieve profile item
     *
     * @param int $itemId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($itemId);
}
