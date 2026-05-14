<?php
 

namespace Abbott\Sarp2\PaymentData\Braintree\PayPalDetails;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Model\Payment\Token;
use Braintree\Transaction\PayPalDetails;
use Aheadworks\Sarp2\PaymentData\Braintree\PayPalDetails\ExpirationDate;

 
class ToToken extends \Aheadworks\Sarp2\PaymentData\Braintree\PayPalDetails\ToToken
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var ExpirationDate
     */
    private $expirationDate;

    /**
     * @param PaymentTokenInterfaceFactory $tokenFactory
     * @param ExpirationDate $expirationDate
     */
    public function __construct(
        PaymentTokenInterfaceFactory $tokenFactory,
        ExpirationDate $expirationDate
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->expirationDate = $expirationDate;
    }

    /**
     * Convert PayPal transaction detail into payment token
     *
     * @param PayPalDetails $payPalDetails
     * @return PaymentTokenInterface
     */
    public function convert($payPalDetails)
    {
        /** @var PaymentTokenInterface $paymentToken */
        $token = ($payPalDetails->token) ? $payPalDetails->token : $payPalDetails->implicitlyVaultedPaymentMethodToken;
        $paymentToken = $this->tokenFactory->create();
        $paymentToken->setPaymentMethod('braintree_paypal')
            ->setType(Token::TOKEN_TYPE_ACCOUNT)
            ->setTokenValue($token)
            ->setDetails('payerEmail', $payPalDetails->payerEmail)
            ->setExpiresAt($this->expirationDate->getFormatted());
        return $paymentToken;
    }
}
