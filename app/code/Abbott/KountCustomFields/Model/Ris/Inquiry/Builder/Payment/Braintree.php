<?php


namespace Abbott\KountCustomFields\Model\Ris\Inquiry\Builder\Payment;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Abbott\KountCustomFields\Model\Ris\Base\Builder\Payment;
use Kount\Kount360\Model\Ris\Inquiry\Builder\Payment\Type;

class Braintree implements PaymentInterface
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
        $bin = $payment->getAdditionalInformation('bin');
        $last4 = $payment->getCcLast4();
        if (!$bin) {
            $nonceToken = $payment->getAdditionalInformation('payment_method_nonce');
            $nonce = $this->adapter->findNonce($nonceToken);
            if (isset($nonce->details)) {
                $bin = isset($nonce->details['bin']) ? $nonce->details['bin'] : null;
                $last4 = isset($nonce->details['lastFour']) ? $nonce->details['lastFour'] : null;
            }
        }
        if ($bin && $last4) {
            $request->setPaymentMasked($bin."XXXXXXXXX".$last4);

            // Set Payment Data to Kount360
            $transactions = $request->getData('transactions');
            $transactionData = end($transactions);

            $transactionData['merchantTransactionId'] = $payment->getLastTransId() ?? '';
            $transactionData['processorMerchantId'] = '';
            $paymentCode = $payment->getMethod() ?? '';
            $transactionData['payment'] = [
                'type' => $this->paymentType->getPaymentType($paymentCode),
                'paymentToken' => $nonceToken,
                'bin' => $bin,
                'last4' => $last4
            ];
            
            $request->setData('transactions', [$transactionData]);

        } else {
            $request->setNoPayment();
        }
    }
}
