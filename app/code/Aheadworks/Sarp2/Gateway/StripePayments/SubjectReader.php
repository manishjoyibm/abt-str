<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments;

use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Magento\Payment\Gateway\Helper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class SubjectReader
 * @package Aheadworks\Sarp2\Gateway\StripePayments
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
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Reads response object from subject
     *
     * @param array $subject
     * @return Response
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);
        if (!isset($response['object']) || !is_object($response['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }

        return $response['object'];
    }

    /**
     * Reads transaction from the subject
     *
     * @param array $subject
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function readTransactionResponse(array $subject)
    {
        if (!isset($subject['object']) || !is_object($subject['object'])) {
            throw new \InvalidArgumentException('Response object does not exist.');
        }

        if (!isset($subject['object']) || !$subject['object'] instanceof Response
        ) {
            throw new \InvalidArgumentException('The object is not a class ' . Response::class);
        }

        return $subject['object'];
    }
}
