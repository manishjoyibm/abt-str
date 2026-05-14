<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Abbott\Sarp2\Gateway\Response;

use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class CardDetailsHandler
 */
class CardDetailsHandler extends \PayPal\Braintree\Gateway\Response\CardDetailsHandler
{
    private const CARD_TYPE = 'cardType';

    private const CARD_EXP_MONTH = 'expirationMonth';

    private const CARD_EXP_YEAR = 'expirationYear';

    private const CARD_LAST4 = 'last4';

    private const CARD_NUMBER = 'cc_number';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transaction = $this->subjectReader->readTransaction($response);

        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $creditCard = $transaction->creditCard;
        if($payment->getMethod() == 'aw_sarp_braintree_googlepay_recurring'){
            $creditCard = $this->getGooglePayCardDetails($transaction->googlePayCardDetails);
        }

        if($payment->getMethod() == 'aw_sarp_braintree_applepay_recurring'){
            $creditCard = $this->getGooglePayCardDetails($transaction->applePayCardDetails);
        }

        $payment->setCcLast4($creditCard[self::CARD_LAST4]);
        $payment->setCcExpMonth($creditCard[self::CARD_EXP_MONTH]);
        $payment->setCcExpYear($creditCard[self::CARD_EXP_YEAR]);

        $payment->setCcType($this->getCreditCardType($creditCard[self::CARD_TYPE]));

        // set card details to additional info
        $payment->setAdditionalInformation(self::CARD_NUMBER, 'xxxx-' . $creditCard[self::CARD_LAST4]);
        $payment->setAdditionalInformation(OrderPaymentInterface::CC_TYPE, $creditCard[self::CARD_TYPE]);
    }

    /**
     * Get type of credit card mapped from Braintree
     *
     * @param string $type
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function getCreditCardType($type): string
    {
        $replaced = str_replace(' ', '-', strtolower($type ?? ''));
        $mapper = $this->config->getCcTypesMapper();

        if(array_key_exists($replaced,$mapper)){
            return $mapper[$replaced];
        }
        else{
            return $replaced;
        }
    }

    public  function  getGooglePayCardDetails($transaction){
        $card = [];
        $card[self::CARD_LAST4] = $transaction->last4;
        $card[self::CARD_EXP_MONTH] = $transaction->expirationMonth;
        $card[self::CARD_EXP_YEAR] = $transaction->expirationYear;
        $card[self::CARD_TYPE] = $transaction->cardType;
        return $card;
    }
}
