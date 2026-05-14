<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileAddressRepositoryInterface;

/**
 * Class AddressHandler
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
class AddressHandler implements HandlerInterface
{
    /**
     * @var ProfileAddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param ProfileAddressRepositoryInterface $addressRepository
     */
    public function __construct(ProfileAddressRepositoryInterface $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ProfileInterface $profile)
    {
        foreach ($profile->getAddresses() as $address) {
            $address->setProfileId($profile->getProfileId());
            $this->addressRepository->save($address);
        }
    }
}
