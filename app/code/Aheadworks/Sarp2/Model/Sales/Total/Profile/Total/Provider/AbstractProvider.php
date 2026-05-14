<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Provider;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Framework\DataObject;

/**
 * Class AbstractProvider
 *
 * @method AbstractProvider setProfile(ProfileInterface $profile)
 *
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Provider
 */
abstract class AbstractProvider extends DataObject implements ProviderInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDiscountAmount($item, $useBaseCurrency)
    {
        return 0;
    }
}
