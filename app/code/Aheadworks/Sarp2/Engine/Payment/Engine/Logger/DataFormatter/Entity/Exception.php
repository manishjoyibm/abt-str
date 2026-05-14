<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity;

use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatterInterface;

/**
 * Class Exception
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity
 */
class Exception implements DataFormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format($subject)
    {
        if ($subject instanceof \Exception) {
            /** @var \Exception $subject */
            return sprintf(
                'Exception %s has been raised with message \'%s\'',
                get_class($subject),
                $subject->getMessage()
            );
        }
        return '';
    }
}
