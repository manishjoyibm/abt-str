<?php


namespace Abbott\KountCustomFields\Model\Ris\Inquiry\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Abbott\KountCustomFields\Model\Ris\Base\Builder\Payment;
use Kount\Kount360\Model\Ris\Inquiry\Builder\Payment\Type;

class BraintreePayPal implements PaymentInterface
{
    /**
     * @var BraintreeAdapter
     */
    protected BraintreeAdapter $adapter;

    /**
     * @var Type
     */
    protected Type $paymentType;    

    /**
     * Braintree constructor.
     * 
     * @param BraintreeAdapter $adapter
     * @param Type $paymentType
     */
    public function __construct(
        BraintreeAdapter $adapter,
        Type $paymentType
    ) {
        $this->adapter = $adapter;
        $this->paymentType = $paymentType;
    }

    /**
     * Method process
     *
     * @param DataObject $request
     * @param OrderPaymentInterface $payment
     * @return void
     */
    public function process(DataObject $request, OrderPaymentInterface $payment): void
    {
        $payPalId = $payment->getAdditionalInformation('payerId');
        if (!$payPalId) {
            $nonceToken = $payment->getAdditionalInformation('payment_method_nonce');
            $nonce = $this->adapter->findNonce($nonceToken);
            if (isset($nonce->details) && isset($nonce->details['payerInfo'])) {
                $payPalId = isset($nonce->details['payerInfo']['payerId']) ?
                    $nonce->details['payerInfo']['payerId'] : null;
            }
        }
        if ($payPalId) {
            $request->setPayPalPayment($payPalId);

            // Set Payment Data to Kount360
            $transactions = $request->getData('transactions');
            $transactionData = end($transactions);

            $transactionData['merchantTransactionId'] = $payment->getLastTransId() ?? '';
            $transactionData['processorMerchantId'] = '';
            $paymentCode = $payment->getMethod() ?? '';
            $transactionData['payment'] = [
                'type' => $this->paymentType->getPaymentType($paymentCode),
                'paymentToken' => $payPalId,
                'bin' => '',
                'last4' => ''
            ];
            $request->setData('transactions', [$transactionData]);

        } else {
            $request->setNoPayment();
        }
    }
}
