<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class SubjectReader
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway
 */
class SubjectReader
{
    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        if (!isset($subject['payment'])
            || !$subject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        return $subject['payment'];
    }

    /**
     * Reads command from subject
     *
     * @param array $subject
     * @return string
     */
    public function readCommand(array $subject)
    {
        if (!isset($subject['command'])
            || empty($subject['command'])
        ) {
            throw new \InvalidArgumentException('Command data object should be provided');
        }

        return $subject['command'];
    }
}
