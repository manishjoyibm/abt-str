<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Integration\Model\Profile;

use Aheadworks\Sarp2\Model\Profile\Validator;

/**
 * Class ValidatorStub
 * @package Aheadworks\Sarp2\Test\Integration\Model\Profile
 */
class ValidatorStub extends Validator
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValid($profile)
    {
        return true;
    }
}
