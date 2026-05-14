<?php


namespace Abbott\Sarp2\Model\Payment;


use Abbott\Sarp2\Model\PaymentChangeLogFactory as PaymentChangeLogFactory;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Finder as SamplerInfoFinder;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Initialization;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Persistence;
use Aheadworks\Sarp2\Model\Payment\Sampler\InfoFactory;
use Aheadworks\Sarp2\Model\Payment\Sampler\Pool as SamplerPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\Data\PaymentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SampleManagement
 * @package Abbott\Sarp2\Model\Payment
 */
class SamplerManagement extends \Aheadworks\Sarp2\Model\Payment\SamplerManagement
{

    const SANDBOX_UNSUCCESSFUL_AMOUNT = 2001;

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
     * @var PaymentChangeLogFactory
     */
    private $paymentChangeLogFactory;

    /**
     * @var \Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog
     */
    private $paymentChangeLogResource;

    /**
     * @var \Abbott\Sarp2\Helper\Data
     */
    private $helper;


    /**
     * SampleManagement constructor.
     * @param SamplerPool $samplerPool
     * @param InfoFactory $samplerInfoFactory
     * @param Initialization $samplerInfoInitialization
     * @param Persistence $persistence
     * @param DataObjectProcessor $dataObjectProcessor
     * @param LoggerInterface $logger
     * @param SamplerInfoFinder $samplerInfoFinder
     * @param PaymentChangeLogFactory $paymentChangeLogFactory
     * @param \Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog $paymentChangeLogResource
     * @param \Abbott\Sarp2\Helper\Data $helper
     */
    public function __construct(
        SamplerPool $samplerPool,
        InfoFactory $samplerInfoFactory,
        Initialization $samplerInfoInitialization,
        Persistence $persistence,
        DataObjectProcessor $dataObjectProcessor,
        LoggerInterface $logger,
        SamplerInfoFinder $samplerInfoFinder,
        PaymentChangeLogFactory $paymentChangeLogFactory,
        \Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog $paymentChangeLogResource,
        \Abbott\Sarp2\Helper\Data $helper
    )
    {
        $this->samplerPool = $samplerPool;
        $this->samplerInfoFactory = $samplerInfoFactory;
        $this->samplerInfoInitialization = $samplerInfoInitialization;
        $this->persistence = $persistence;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->logger = $logger;
        $this->samplerInfoFinder = $samplerInfoFinder;
        $this->paymentChangeLogFactory = $paymentChangeLogFactory;
        $this->paymentChangeLogResource = $paymentChangeLogResource;
        $this->helper = $helper;
        parent::__construct($samplerPool, $samplerInfoFactory, $samplerInfoInitialization, $persistence, $dataObjectProcessor, $logger, $samplerInfoFinder);
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

            if($this->helper->getTestUnsuccessfulTransaction()) {
                $samplerInfo->setAmount(self::SANDBOX_UNSUCCESSFUL_AMOUNT);
            }

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
        } catch (\Magento\Payment\Gateway\Command\CommandException $e) {
            $paymentChangeLog = $this->paymentChangeLogFactory->create();
            $paymentChangeLog->setCustomerId($profile->getCustomerId());
            $paymentChangeLog->setProfileId($profile->getProfileId());
            $paymentChangeLog->setHasFailed(true);
            $this->paymentChangeLogResource->save($paymentChangeLog);
            if ($samplerInfo->getId()) {
                $this->persistence->delete($samplerInfo);
            }
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
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
