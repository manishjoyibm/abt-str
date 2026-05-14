<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type;

use Aheadworks\Sarp2\Engine\Exception\PaymentException;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Failure\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status as ProfileStatus;

/**
 * Class Actual
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type
 */
class Actual extends AbstractPayProcessor
{
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
                file_put_contents(BP.'/var/log/similacerror.log',  $e->getMessage() ,FILE_APPEND);
                $payment->setPaymentStatus(PaymentInterface::STATUS_CANCELLED);
                $profile = $payment->getProfile();
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
        return $this->isProcessableChecker->check($payment, PaymentInterface::TYPE_ACTUAL);
    }
}
