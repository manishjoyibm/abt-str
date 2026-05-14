<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Initialization;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Persistence;
use Aheadworks\Sarp2\Model\Payment\Sampler\InfoFactory;
use Aheadworks\Sarp2\Model\Payment\Sampler\Pool as SamplerPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\Data\PaymentInterface;
use Psr\Log\LoggerInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Finder as SamplerInfoFinder;

/**
 * Class SamplerManagement
 * @package Aheadworks\Sarp2\Model\Payment
 */
class SamplerManagement
{
    /**
     * Maximum revert operations count
     */
    const MAX_REVERT_OPERATIONS_COUNT = 10;

    /**
     * @var SamplerPool
     */
    private $samplerPool;

    /**
     * @var InfoFactory
     */
    private $samplerInfoFactory;

    /**
     * @var Initialization
     */
    private $samplerInfoInitialization;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SamplerInfoFinder
     */
    private $samplerInfoFinder;

    /**
     * @param SamplerPool $samplerPool
     * @param InfoFactory $samplerInfoFactory
     * @param Initialization $samplerInfoInitialization
     * @param Persistence $persistence
     * @param DataObjectProcessor $dataObjectProcessor
     * @param LoggerInterface $logger
     * @param SamplerInfoFinder $samplerInfoFinder
     */
    public function __construct(
        SamplerPool $samplerPool,
        InfoFactory $samplerInfoFactory,
        Initialization $samplerInfoInitialization,
        Persistence $persistence,
        DataObjectProcessor $dataObjectProcessor,
        LoggerInterface $logger,
        SamplerInfoFinder $samplerInfoFinder
    ) {
        $this->samplerPool = $samplerPool;
        $this->samplerInfoFactory = $samplerInfoFactory;
        $this->samplerInfoInitialization = $samplerInfoInitialization;
        $this->persistence = $persistence;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->logger = $logger;
        $this->samplerInfoFinder = $samplerInfoFinder;
    }

    /**
     * Submit payment
     *
     * @param ProfileInterface $profile
     * @param PaymentInterface $payment
     * @return Info
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function submitPayment($profile, PaymentInterface $payment)
    {
        /** @var Info $samplerInfo */
        $samplerInfo = $this->samplerInfoFactory->create();
        $this->samplerInfoInitialization->init($samplerInfo, $payment);
        $samplerInfo
            ->setProfileId($profile->getProfileId())
            ->setProfile($profile);

        try {
            $this->persistence->save($samplerInfo);

            $sampler = $this->samplerPool->getSampler($payment->getMethod());
            $sampler->importPayment(
                $samplerInfo,
                $this->getPaymentData($payment)
            );
            $sampler->place($samplerInfo);
            $additionalInformation = $samplerInfo->getAdditionalInformation();
            if (!$additionalInformation
                || ($additionalInformation
                    && !isset($additionalInformation['aw_sarp_payment_token_id'])
                    && !isset($additionalInformation['aw_sarp_skip_payment_token'])
                )
            ) {
                throw new LocalizedException(__('Token can\'t be received.'));
            }
            $this->persistence->save($samplerInfo);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            if ($samplerInfo->getId()) {
                $this->persistence->delete($samplerInfo);
            }

            throw new CouldNotSaveException(
                __(
                    'A server error stopped your payment details from being saved.'
                ),
                $e
            );
        }

        return $samplerInfo;
    }

    /**
     * Revert payments
     */
    public function revertPayments()
    {
        $samplerInfoArray = $this->samplerInfoFinder->getSamplePaymentsToRevert(
            self::MAX_REVERT_OPERATIONS_COUNT
        );

        /** @var Info $samplerInfo */
        foreach ($samplerInfoArray as $samplerInfo) {
            $this->revertPaymentBySamplerInfo($samplerInfo);
        }
    }

    /**
     * Revert specific payment by its sampler info
     *
     * @param Info$samplerInfo
     * @return Info|null
     */
    public function revertPaymentBySamplerInfo($samplerInfo)
    {
        try {
            $paymentMethodCode = $samplerInfo->getMethod();
            $sampler = $this->samplerPool->getSampler($paymentMethodCode);
            $sampler->revert($samplerInfo);
            $this->persistence->save($samplerInfo);
            return $samplerInfo;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return null;
        }
    }

    /**
     * Get payment data array
     *
     * @param PaymentInterface $payment
     * @return array
     */
    private function getPaymentData(PaymentInterface $payment)
    {
        $data = $this->dataObjectProcessor->buildOutputDataArray(
            $payment,
            PaymentInterface::class
        );
        $data[PaymentInterface::KEY_ADDITIONAL_DATA] = $payment->getAdditionalData();
        return $data;
    }
}
