<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type;

use Abbott\Sarp2\Helper\ChangeSubscription;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Engine\Exception\PaymentException;
use Aheadworks\Sarp2\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\ActionInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\Pool;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface as EngineLogger;
use Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Factory as HandlerFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Source;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Next;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\ProcessorInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\ItemFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class AbstractPayProcessor
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type
 */
abstract class AbstractPayProcessor implements ProcessorInterface
{
    /**
     * @var Pool
     */
    private $actionPool;

    /**
     * @var Incrementor
     */
    protected $stateIncrementor;

    /**
     * @var Persistence
     */
    protected $persistence;

    /**
     * @var Detector
     */
    protected $outstandingDetector;

    /**
     * @var SourceFactory
     */
    private $generatorSourceFactory;

    /**
     * @var Next
     */
    private $generator;

    /**
     * @var HandlerFactory
     */
    protected $failureHandlerFactory;

    /**
     * @var IsProcessable
     */
    protected $isProcessableChecker;

    /**
     * @var Cleaner
     */
    protected $cleaner;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Manager
     */
    protected $notificationManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var EngineLogger
     */
    protected $engineLogger;

    protected $product;

    protected $itemFactory;

    protected $profileManagement;

    protected $helper;

    /**
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
     * @param LoggerInterface $logger
     * @param Config $config
     * @param EngineLogger $engineLogger
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
        LoggerInterface $logger,
        Config $config,
        EngineLogger $engineLogger,
        ProductRepositoryInterface $product,
        ItemFactory $itemFactory,
        ProfileManagementInterface $profileManagement,
        ChangeSubscription $helper

    ) {
        $this->actionPool = $actionPool;
        $this->stateIncrementor = $stateIncrementor;
        $this->persistence = $persistence;
        $this->outstandingDetector = $outstandingDetector;
        $this->generator = $generator;
        $this->generatorSourceFactory = $generatorSourceFactory;
        $this->failureHandlerFactory = $failureHandlerFactory;
        $this->isProcessableChecker = $isProcessableChecker;
        $this->cleaner = $cleaner;
        $this->resultFactory = $resultFactory;
        $this->notificationManager = $notificationManager;
        $this->logger = $logger;
        $this->config = $config;
        $this->engineLogger = $engineLogger;
        $this->product = $product;
        $this->itemFactory = $itemFactory;
        $this->profileManagement = $profileManagement;
        $this->helper = $helper;
    }

    /**
     * Perform pay action
     *
     * @param Payment $payment
     * @return Payment
     */
    protected function pay($payment)
    {
        $notification = $this->notificationManager->schedule(
            NotificationInterface::TYPE_BILLING_SUCCESSFUL,
            $payment
        );

        $profile = $payment->getProfile();

        $outOfStockItems = $this->checkProfileItemsStockStaus($profile);
        if (count($outOfStockItems) > 0 && count($profile->getItems()) == 1) {
            $this->cancelSubscription($profile);
            return $payment;
        }

        if (count($outOfStockItems) > 0 && count($profile->getItems()) > 1) {
            $this->removeOutOfStockItem($profile, $outOfStockItems);
        }

		if (count($outOfStockItems) > 0 && count($profile->getItems()) == 0) {
            $this->cancelSubscription($profile);
            return $payment;
        }

        $profile = $payment->getProfile();
        $isBundled = $payment->isBundled();
        $actionType = $isBundled
        ? ActionInterface::TYPE_BUNDLED
        : ActionInterface::TYPE_SINGLE;
        $action = $this->actionPool->getAction($actionType);
        $result = $action->pay($payment);

        $order = $result->getOrder();
        $orderId = $order->getEntityId();
        $payment->setPaymentStatus(PaymentInterface::STATUS_PAID)
            ->setOrderId($orderId)
            ->setTotalPaid($order->getGrandTotal())
            ->setBaseTotalPaid($order->getBaseGrandTotal())
            ->setPaidAt($order->getCreatedAt());

        $this->increment($payment);

        $this->logPaymentSuccess($payment, $result);

        /** @var Source $source */
        $source = $this->generatorSourceFactory->create(
            [
                'payments' => $isBundled
                ? $payment->getChildItems()
                : [$payment],
            ]
        );

        $nextPayments = $this->generator->generate($source);
        $this->persist($nextPayments);

        if (count($nextPayments) && $this->config->isLogEnabled()) {
            $this->engineLogger->traceProcessing(
                EngineLogger::ENTRY_PAYMENTS_SCHEDULED,
                ['payment' => $payment],
                ['scheduledPayments' => $nextPayments]
            );
        }

        /**
         * @param PaymentInterface $nextPayment
         * @return void
         */
        $scheduleNotificationCallback = function ($nextPayment) {
            $this->notificationManager->schedule(
                NotificationInterface::TYPE_UPCOMING_BILLING,
                $nextPayment
            );
        };
        array_walk($nextPayments, $scheduleNotificationCallback);

        if ($isBundled) {
            $this->cleaner->addList($payment->getChildItems());
        }

        if ($notification) {
            $subjectData = ['sourcePayment' => $payment];
            if (count($nextPayments)) {
                $subjectData['nextPayments'] = $nextPayments;
            }

            $this->notificationManager->updateNotificationData(
                $notification->setOrderId($orderId),
                $subjectData
            );
        }

        return $payment;

    }

    /**
     * Increment state
     *
     * @param PaymentInterface|Payment $payment
     */
    protected function increment($payment)
    {
        $this->stateIncrementor->increment($payment);
        $this->persist([$payment]);
    }

    /**
     * Persist payments
     *
     * @param PaymentInterface[]|Payment[] $payments
     * @return void
     */
    protected function persist($payments)
    {
        foreach ($payments as $payment) {
            if ($payment->isBundled()) {
                $this->persistence->massSave(array_merge([$payment], $payment->getChildItems()));
            } else {
                $this->persistence->save($payment);
            }
        }
    }

    /**
     * Add successful payment log record
     *
     * @param PaymentInterface $payment
     * @param ResultInterface $paymentResult
     * @return void
     */
    private function logPaymentSuccess($payment, $paymentResult)
    {
        if ($this->config->isLogEnabled()) {
            $this->logger->info(
                'Payment successful',
                [
                    'orderId' => $paymentResult->getOrder()->getEntityId(),
                    'paymentId' => $payment->getId(),
                ]
            );
            $this->engineLogger->traceProcessing(
                EngineLogger::ENTRY_PAYMENT_SUCCESSFUL,
                [
                    'payment' => $payment,
                    'result' => $paymentResult,
                ]
            );
        }
    }

    /**
     * Add payment failure log record
     *
     * @param string $message
     * @param PaymentException $exception
     * @param PaymentInterface $payment
     * @param array $context
     * @return void
     */
    protected function logPaymentFailure($message, $exception, $payment, $context = [])
    {
        if ($this->config->isLogEnabled()) {
            $context = array_merge(
                ['message' => $exception->getLogMessage()],
                $context
            );
            $exceptionCode = $exception->getCode();
            if ($exceptionCode) {
                $context['code'] = $exceptionCode;
            }
            if (!$payment->isBundled()) {
                $context['profileId'] = $payment->getProfileId();
            } else {
                $profileIds = [];
                foreach ($payment->getChildItems() as $child) {
                    $profileIds[] = $child->getProfileId();
                }
                $context['profileIds'] = $profileIds;
            }

            $this->logger->error($message, $context);
        }
    }

    /**
     * @param  Magento\Catalog\Api\ProductRepositoryInterface
     * @return void
     */
    protected function checkProfileItemsStockStaus($profile)
    {
        try {
            $outOfStockItems = [];
            foreach ($profile->getItems() as $item) {
                $productId = $item->getProductId();
                $_product = $this->product->get($item->getSku());
                if (!$_product->isAvailable()) {
                    $outOfStockItems[] = $item->getSku();
                }
            }
            return $outOfStockItems;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['profileId' => $profile->getId()]);
        }
    }

     /**
     * @param  Magento\Catalog\Api\ProductRepositoryInterface
     * @return void
     */
    protected function cancelSubscription($profile)
    {
        try {
			$this->profileManagement->changeStatusAction($profile->getId(), Status::CANCELLED);
			$this->helper->cancelSubscriptionNotification($profile->getId());
			$this->logger->info(
				'Profile Items Out Of Stock, Hence Cancelling the Subscription',
				[
					'profileId' => $profile->getId(),
				]
			);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['profileId' => $profile->getId()]);
        }
    }

     /**
     * @param  Magento\Catalog\Api\ProductRepositoryInterface
     * @Array outOfStockItems
     * @return void
     */
    protected function removeOutOfStockItem($profile, $outOfStockItems)
    {
        try {
            $profileId = $profile->getId();
            $profileItems = [];
            if (count($outOfStockItems) > 0) {
                foreach ($outOfStockItems as $itemSku) {
                    $collection = $this->itemFactory->create()->getCollection()
                        ->addFieldToFilter('profile_id', ['eq' => $profileId])
                        ->addFieldToFilter('sku', ['eq' => $itemSku]);
                    if ($collection->getSize() == 1) {
                        $collection->walk('delete');
                        $collection->save();
                    }
                }

				if(count($outOfStockItems) != count($profile->getItems())) {
					$this->helper->sendItemOutOfStockNotification($profile->getId(), $outOfStockItems);
				}

                $profileCollection = $this->itemFactory->create()->getCollection()
                    ->addFieldToFilter('profile_id', ['eq' => $profileId]);

                if ($profileCollection->getSize()) {
                    foreach ($profileCollection as $profileItem) {
                        $profileItems[] = $profileItem;
                    }
                }
				$profile->setItems($profileItems)->setItemsQty(count($profileItems));
                $profile->save();

                $this->logger->info(
                    'Profile Items Out Of Stock, Hence Removing the Item from Subscription',
                    [
                        'profileId' => $profile->getId(),
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['profileId' => $profile->getId()]);
        }

    }

}
