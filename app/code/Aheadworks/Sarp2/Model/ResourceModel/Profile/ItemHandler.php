<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileItemRepositoryInterface;

/**
 * Class ItemHandler
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
class ItemHandler implements HandlerInterface
{
    /**
     * @var ProfileItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @param ProfileItemRepositoryInterface $itemRepository
     */
    public function __construct(ProfileItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ProfileInterface $profile)
    {
        foreach ($profile->getItems() as $item) {
            $item->setProfileId($profile->getProfileId());
            $this->itemRepository->save($item);
        }
    }
}
