<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree\Request;

use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Observer\BraintreeRecurring\DataAssignObserver;
use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Gateway\Request\PaymentDataBuilder as BraintreeBuilder;
use Aheadworks\Sarp2\Gateway\Braintree\SubjectReaderFactory;
use Aheadworks\Sarp2\Gateway\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class PaymentDataBuilder
 * @package Aheadworks\Sarp2\Gateway\Braintree\Request
 */
class PaymentDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var BraintreeAdapterFactory
     */
    private $braintreeAdapterFactory;

    /**
     * @param Config $config
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param BraintreeAdapterFactory $braintreeAdapterFactory
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        Config $config,
        SubjectReaderFactory $subjectReaderFactory,
        BraintreeAdapterFactory $braintreeAdapterFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->config = $config;
        $this->subjectReaderFactory = $subjectReaderFactory;
        $this->braintreeAdapterFactory = $braintreeAdapterFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $subjectReader = $this->subjectReaderFactory->getInstance();
        $paymentDO = $subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $storeId = $order->getStoreId();

        $result = [
            BraintreeBuilder::AMOUNT => $this->formatPrice($subjectReader->readAmount($buildSubject)),
            BraintreeBuilder::ORDER_ID => $order->getOrderIncrementId()
        ];

        $paymentTokenId = $payment->getAdditionalInformation(DataAssignObserver::PAYMENT_TOKEN_ID);
        if (!$paymentTokenId) {
            throw new \LogicException('Payment token Id does not specified.');
        }
        $gatewayToken = $this->paymentTokenRepository->get($paymentTokenId)
            ->getTokenValue();

        $adapter = $this->braintreeAdapterFactory->getInstance($storeId);
        $nonceCreateResult = $adapter->createNonce($gatewayToken);
        $result[BraintreeBuilder::PAYMENT_METHOD_NONCE] = $nonceCreateResult->paymentMethodNonce->nonce;

        $merchantAccountId = $this->config->getMerchantAccountId($storeId);
        if (!empty($merchantAccountId)) {
            $result[BraintreeBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
        }

        return $result;
    }
}
