<?php

namespace Abbott\FedexTracking\Gateway\Response;

use Braintree\Transaction;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;


class TransactionIdHandler extends \PayPal\Braintree\Gateway\Response\TransactionIdHandler
{
    const CAPTURE_ID = 'captureId';
    const PAYPAL_METHOD = 'braintree_paypal';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * TransactionIdHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var Transaction $transaction */
            $transaction = $this->subjectReader->readTransaction($response);

            /** @var Payment $orderPayment */
            $orderPayment = $paymentDO->getPayment();
            $this->setTransactionId(
                $orderPayment,
                $transaction
            );
            if ($orderPayment->getMethod() == self::PAYPAL_METHOD) {
                $payPal = $this->subjectReader->readPayPal($transaction);
                $orderPayment->setAdditionalInformation(self::CAPTURE_ID, $payPal[self::CAPTURE_ID]);
            }
            $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
            $closed = $this->shouldCloseParentTransaction($orderPayment);
            $orderPayment->setShouldCloseParentTransaction($closed);
        }
    }
}
