<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Adapter\StripePayments\StripeObject;

use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\ResponseFactory;
use Stripe\PaymentIntent;

/**
 * Class Converter
 * @package Aheadworks\Sarp2\Model\Adapter\StripePayments\StripeObject
 */
class Converter
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Convert payment intent to response
     *
     * @param PaymentIntent $paymentIntent
     * @return Response
     */
    public function toResponse($paymentIntent)
    {
        $responseData = $paymentIntent->__toArray();
        $response = $this->responseFactory->create(['data' => $responseData]);

        return $response;
    }
}
