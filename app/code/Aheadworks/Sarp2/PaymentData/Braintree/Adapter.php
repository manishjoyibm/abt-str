<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree;

use Aheadworks\Sarp2\PaymentData\AdapterInterface;
use Aheadworks\Sarp2\PaymentData\Braintree\Adapter\ClientFactory;
use Aheadworks\Sarp2\PaymentData\Braintree\Adapter\RequestDataBuilder;
use Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails\ToCreateResult;
use Aheadworks\Sarp2\PaymentData\PaymentInterface;
use PayPal\Braintree\Gateway\Http\TransferFactory;

/**
 * Class Adapter
 * @package Aheadworks\Sarp2\PaymentData\Braintree
 */
class Adapter implements AdapterInterface
{
    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var RequestDataBuilder
     */
    private $requestBuilder;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var ToCreateResult
     */
    private $cardDetailsConverter;

    /**
     * @param TransferFactory $transferFactory
     * @param RequestDataBuilder $requestBuilder
     * @param ClientFactory $clientFactory
     * @param ToCreateResult $cardDetailsConverter
     */
    public function __construct(
        TransferFactory $transferFactory,
        RequestDataBuilder $requestBuilder,
        ClientFactory $clientFactory,
        ToCreateResult $cardDetailsConverter
    ) {
        $this->transferFactory = $transferFactory;
        $this->requestBuilder = $requestBuilder;
        $this->clientFactory = $clientFactory;
        $this->cardDetailsConverter = $cardDetailsConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function create(PaymentInterface $payment)
    {
        $request = $this->requestBuilder->setCommand(RequestDataBuilder::COMMAND_CREATE)
            ->setPayment($payment)
            ->build();
        $transfer = $this->transferFactory->create($request);
        $client = $this->clientFactory->create($payment->getQuote()->getStoreId());
        $response = $client->createCustomer($transfer);
        return $this->cardDetailsConverter->convert($response['object']->paymentMethods[0]);
    }
}
