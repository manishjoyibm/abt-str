<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\Adapter;

use Aheadworks\Sarp2\PaymentData\PaymentInterface;
use PayPal\Braintree\Observer\DataAssignObserver;

/**
 * Class RequestDataBuilder
 * @package Aheadworks\Sarp2\PaymentData\Braintree\Adapter
 */
class RequestDataBuilder
{
    /**
     * Customer first name data field
     */
    const DATA_CUSTOMER_FIRST_NAME = 'firstName';

    /**
     * Customer last name data field
     */
    const DATA_CUSTOMER_LAST_NAME = 'lastName';

    /**
     * Customer company data field
     */
    const DATA_CUSTOMER_COMPANY = 'company';

    /**
     * Customer email data field
     */
    const DATA_CUSTOMER_EMAIL = 'email';

    /**
     * Customer phone data field
     */
    const DATA_CUSTOMER_PHONE = 'phone';

    /**
     * Payment method nonce data field
     */
    const DATA_PAYMENT_METHOD_NONCE = 'paymentMethodNonce';

    /**
     * Create command
     */
    const COMMAND_CREATE = 'create';

    /**
     * @var string
     */
    private $command;

    /**
     * @var PaymentInterface
     */
    private $payment;

    /**
     * Set command type
     *
     * @param string $command
     * @return $this
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * Set payment
     *
     * @param PaymentInterface $payment
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * Build request
     *
     * @return array|null
     */
    public function build()
    {
        $result = null;
        if ($this->isStateValid()) {
            if ($this->command == self::COMMAND_CREATE) {
                $result = $this->buildCreateRequest();
            }
        }
        $this->resetState();
        return $result;
    }

    /**
     * Build create request
     *
     * @return array
     */
    private function buildCreateRequest()
    {
        $billingAddress = $this->payment->getQuote()->getBillingAddress();
        return [
            self::DATA_CUSTOMER_FIRST_NAME => $billingAddress->getFirstname(),
            self::DATA_CUSTOMER_LAST_NAME => $billingAddress->getLastname(),
            self::DATA_CUSTOMER_COMPANY => $billingAddress->getCompany(),
            self::DATA_CUSTOMER_PHONE => $billingAddress->getTelephone(),
            self::DATA_CUSTOMER_EMAIL => $billingAddress->getEmail(),
            self::DATA_PAYMENT_METHOD_NONCE => $this->payment->getPaymentInfo()
                ->getAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE)
        ];
    }

    /**
     * Check if state is valid for build
     *
     * @return bool
     */
    private function isStateValid()
    {
        if (isset($this->command)) {
            if ($this->command == self::COMMAND_CREATE) {
                return isset($this->payment);
            }
        }
        return false;
    }

    /**
     * Reset state
     *
     * @return void
     */
    private function resetState()
    {
        $this->command = null;
        $this->payment = null;
    }
}
