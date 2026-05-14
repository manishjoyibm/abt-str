<?php


namespace Abbott\Subscriptionhistory\Engine\Payment\Processor\Type;

use Abbott\Sarp2\Helper\ChangeSubscription;
use Abbott\Subscriptionhistory\Helper\HistoryMessages;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Engine\Exception\PaymentException;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Action\Pool;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Factory as HandlerFactory;
use Aheadworks\Sarp2\Engine\Payment\Failure\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Next;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\ItemFactory;
use Aheadworks\Sarp2\Model\Profile\Source\Status as ProfileStatus;
use Abbott\Subscriptionhistory\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Reattempt extends \Aheadworks\Sarp2\Engine\Payment\Processor\Type\Reattempt
{
    /**
     * @var Data
     */
    protected $subscriptionHelper;
    /**
     * @var \Abbott\Sarp2\Helper\Data
     */
    protected $sarpHelper;

    /**
     * Reattempt constructor.
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
     * @param Data $subscriptionHistory
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
        Data $subscriptionHistory,
        \Abbott\Sarp2\Helper\Data $sarpHelper
    ) {
        $this->subscriptionHelper = $subscriptionHistory;
        $this->sarpHelper = $sarpHelper;
        parent::__construct(
            $actionPool,
            $stateIncrementor,
            $persistence,
            $outstandingDetector,
            $generator,
            $generatorSourceFactory,
            $failureHandlerFactory,
            $isProcessableChecker,
            $cleaner,
            $resultFactory,
            $notificationManager,
            $logger,
            $config,
            $engineLogger,
            $product,
            $itemFactory,
            $profileManagement,
            $helper
        );
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
            $this->persistence->massChangeStatusAndType(
                $outstandingPayments,
                PaymentInterface::STATUS_RETRYING,
                PaymentInterface::TYPE_OUTSTANDING
            );
            $this->engineLogger->traceProcessing(
                LoggerInterface::ENTRY_PAYMENTS_STATUS_AND_TYPE_MASS_CHANGE,
                ['payments' => $payments],
                ['updatedPayments' => $outstandingPayments]
            );
        }

        /** @var Payment $payment */
        foreach ($outstandingDetect->getTodayPayments() as $payment) {
            try {
                $this->pay($payment);
                if ($payment->getOrderId()) {
                    $this->sarpHelper->sendOrderConfirmationEmail($payment);
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
                $failureHandler->handleReattempt($payment); // Saving is a part of handling logic

                $this->notificationManager->schedule(
                    NotificationInterface::TYPE_BILLING_FAILED,
                    $payment
                );
                $this->logPaymentFailure(
                    'Payment reattempt failed',
                    $e,
                    $payment,
                    [
                        'paymentId' => $payment->getId(),
                        'Payment attempts left' => HandlerInterface::MAX_PAYMENT_FAILURES - $payment->getRetriesCount(),
                        'paymentStatus' => $payment->getPaymentStatus()
                    ]
                );
            } catch (\Exception $e) {
                $payment->setPaymentStatus(PaymentInterface::STATUS_CANCELLED);
                $profile = $payment->getProfile();

                if ($this->subscriptionHelper->getSubscriptionHistoryStatus($payment->getProfile()->getStoreId())) {
                    $this->subscriptionHelper->getProfileStateBeforeData(
                        $payment->getProfile(),
                        HistoryMessages::CRON_PROFILE_CANCELLED
                    );
                }
                if ($profile) {
                    $profile->setStatus(ProfileStatus::CANCELLED);
                }
                $this->persistence->save($payment);

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
        return $this->isProcessableChecker->check($payment, PaymentInterface::TYPE_REATTEMPT);
    }
}
