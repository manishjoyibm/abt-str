<?php

namespace Abbott\Sarp2\Engine\Payment\Processor\Type;

use Abbott\Sarp2\Helper\ChangeSubscription;
use Abbott\Subscriptionhistory\Helper\HistoryMessages;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Engine\Exception\PaymentException;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Action\Pool;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Factory as HandlerFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Next;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Failure\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\ItemFactory;
use Aheadworks\Sarp2\Model\Profile\Source\Status as ProfileStatus;
use Abbott\Subscriptionhistory\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class Actual
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type
 */
class Actual extends \Aheadworks\Sarp2\Engine\Payment\Processor\Type\Actual
{

    /**
     * @var Data
     */
    protected  $_subscriptionHelper;

    /**
     * Actual constructor.
     * @param Pool $actionPool
     * @param Incrementor $stateIncrementor
     * @param Persistence $persistence
     * @param Detector $outstandingDetector
     * @param Next $generator
     * @param SourceFactory $generatorSourceFactory
     * @param HandlerFactory $failureHandlerFactory
     * @param IsProcessable $isProcessableChecker
     * @param Cleaner $cleaner
     * @param ResultFactory $resultFactory
     * @param Manager $notificationManager
     * @param \Aheadworks\Sarp2\Engine\LoggerInterface $logger
     * @param Config $config
     * @param LoggerInterface $engineLogger
     * @param ProductRepositoryInterface $product
     * @param ItemFactory $itemFactory
     * @param ProfileManagementInterface $profileManagement
     * @param ChangeSubscription $helper
     * @param Data $subscriptionHelper
     */
    public function __construct(
        Pool $actionPool,
        Incrementor $stateIncrementor,
        Persistence $persistence,
        Detector $outstandingDetector,
        Next $generator,
        SourceFactory $generatorSourceFactory,
        HandlerFactory $failureHandlerFactory,
        IsProcessable $isProcessableChecker,
        Cleaner $cleaner,
        ResultFactory $resultFactory,
        Manager $notificationManager,
        \Aheadworks\Sarp2\Engine\LoggerInterface $logger,
        Config $config,
        LoggerInterface $engineLogger,
        ProductRepositoryInterface $product,
        ItemFactory $itemFactory,
        ProfileManagementInterface $profileManagement,
        ChangeSubscription $helper,
        Data $subscriptionHelper
    )
    {
        $this->_subscriptionHelper = $subscriptionHelper;
        parent::__construct($actionPool, $stateIncrementor, $persistence, $outstandingDetector, $generator, $generatorSourceFactory, $failureHandlerFactory, $isProcessableChecker, $cleaner, $resultFactory, $notificationManager, $this->logger = $logger, $config, $engineLogger, $product, $itemFactory, $profileManagement, $helper);
    }

    /**
     * {@inheritdoc}
     */
    public function process($payments)
    {   
        $payments = array_filter($payments, [$this, 'isProcessable']);

        $outstandingDetect = $this->outstandingDetector->detect($payments);
        $outstandingPayments = $outstandingDetect->getOutstandingPayments();
        if (count($outstandingPayments)) {
            $this->persistence->massChangeType($outstandingPayments, PaymentInterface::TYPE_OUTSTANDING);
            $this->engineLogger->traceProcessing(
                LoggerInterface::ENTRY_PAYMENTS_TYPE_MASS_CHANGE,
                ['payments' => $payments],
                ['updatedPayments' => $outstandingPayments]
            );
        }

        /** @var Payment $payment */
        foreach ($outstandingDetect->getTodayPayments() as $payment) {
            try {
                $this->pay($payment);
                if($payment->getOrderId()) {
                	\Magento\Framework\App\ObjectManager::getInstance()->get(\Abbott\Sarp2\Helper\Data::class)->sendOrderConfirmationEmail($payment);
                }
            } catch (PaymentException $e) {
                if ($this->config->isLogEnabled()) {
                    $this->engineLogger->traceProcessing(
                        LoggerInterface::ENTRY_PAYMENT_FAILED,
                        ['payments' => $payments],
                        ['failedPayment' => $payment, 'exception' => $e]
                    );
                }

                $handlerType = $payment->isBundled()
                    ? HandlerInterface::TYPE_BUNDLE
                    : HandlerInterface::TYPE_SINGLE;
                $failureHandler = $this->failureHandlerFactory->create($handlerType);
                $failureHandler->handle($payment); // Saving is a part of handling logic

                $this->cleaner->add($payment);
                $this->notificationManager->schedule(
                    NotificationInterface::TYPE_BILLING_FAILED,
                    $payment
                );
                $this->logPaymentFailure(
                    'Payment failed',
                    $e,
                    $payment,
                    ['Payment attempts left' => HandlerInterface::MAX_PAYMENT_FAILURES - 1]
                );
            } catch (\Exception $e) {
                
                $this->logger->error($e->getMessage());

                if($this->_subscriptionHelper->getSubscriptionHistoryStatus($payment->getProfile()->getStoreId())){
                    $this->_subscriptionHelper->getProfileStateBeforeData($payment->getProfile(),HistoryMessages::CRON_PROFILE_CANCELLED);
                }

                $payment->setPaymentStatus(PaymentInterface::STATUS_CANCELLED);
                $profile = $payment->getProfile();
                if ($profile) {
                    $profile->setStatus(ProfileStatus::CANCELLED);
                }
                $this->persistence->save($payment);

                if($this->_subscriptionHelper->getSubscriptionHistoryStatus($payment->getProfile()->getStoreId())){
                    $data[HistoryMessages::CRON_PROFILE_CANCELLED] = $this->_subscriptionHelper->compareProfileStatus(HistoryMessages::CRON_PROFILE_CANCELLED, $profile);
                    $this->_subscriptionHelper->saveSubscriptionHistoryLog($profile, HistoryMessages::CRON_PROFILE_CANCELLED, array(HistoryMessages::CRON_PROFILE_CANCELLED => HistoryMessages::CRON_PROFILE_CANCELLED), $data);
                }


                $this->engineLogger->traceProcessing(
                    LoggerInterface::ENTRY_PAYMENT_STATUS_CHANGE,
                    ['payments' => $payments],
                    ['exception' => $e]
                );
            }
        }

        return $this->resultFactory->create(
            ['isOutstandingDetected' => (count($outstandingPayments) > 0)]
        );
    }

    /**
     * Check if payment is processable
     *
     * @param $payment
     * @return bool
     */
    private function isProcessable($payment)
    {
        return $this->isProcessableChecker->check($payment, PaymentInterface::TYPE_ACTUAL);
    }
}
