<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Adapter;

use Aheadworks\Sarp2\Gateway\StripePayments\Config\Config as StripeConfig;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\StripeObject\Converter as StripeObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeObject;
use Stripe\Customer as StripeCustomer;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\CustomerManagerFactory as StripePaymentsCustomerManagerFactory;

/**
 * Class StripePayments
 * @package Aheadworks\Sarp2\Model\Adapter
 */
class StripePayments
{
    /**
     * @var StripeConfig
     */
    private $config;

    /**
     * @var StripeObjectConverter
     */
    private $stripeObjectConverter;

    /**
     * @var StripePaymentsCustomerManagerFactory
     */
    private $stripePaymentsCustomerManagerFactory;

    /**
     * @param StripeConfig $stripeConfig
     * @param StripeObjectConverter $stripeObjectConverter
     * @param StripePaymentsCustomerManagerFactory $stripePaymentsCustomerManagerFactory
     */
    public function __construct(
        StripeConfig $stripeConfig,
        StripeObjectConverter $stripeObjectConverter,
        StripePaymentsCustomerManagerFactory $stripePaymentsCustomerManagerFactory
    ) {
        $this->config = $stripeConfig;
        $this->stripeObjectConverter = $stripeObjectConverter;
        $this->stripePaymentsCustomerManagerFactory = $stripePaymentsCustomerManagerFactory;
    }

    /**
     * Single payment request
     *
     * @param array $data
     * @return Response
     * @throws \Exception
     */
    public function singlePayment(array $data)
    {
        $this->initStripe();
        $intent = PaymentIntent::create($data);
        $intent->confirm();

        return $this->stripeObjectConverter->toResponse($intent);
    }

    /**
     * Capture request
     *
     * @param string $id
     * @param float|null $amount
     * @return Response
     * @throws \Exception
     */
    public function capture($id, $amount = null)
    {
        $this->initStripe();
        $intent = PaymentIntent::retrieve($id);

        if ($amount) {
            $intent->capture(['amount_to_capture' => $amount]);
        } else {
            $intent->capture();
        }

        return $this->stripeObjectConverter->toResponse($intent);
    }

    /**
     * Void request
     *
     * @param string $id
     * @return Response
     * @throws \Exception
     */
    public function void($id)
    {
        $this->initStripe();
        $result = $this->cancel($id);

        return $this->stripeObjectConverter->toResponse($result);
    }

    /**
     * Refund request
     *
     * @param string $id
     * @param float|null $amount
     * @return Response
     * @throws \Exception
     */
    public function refund($id, $amount = null)
    {
        $this->initStripe();
        $result = $this->cancel($id, $amount);

        return $this->stripeObjectConverter->toResponse($result);
    }

    /**
     * Retrieve current Stripe customer
     *
     * @param int|null $customerId
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @return StripeCustomer|null
     * @throws \Exception
     */
    public function getCurrentCustomer($customerId, $email, $firstname, $lastname)
    {
        $currentCustomer = null;
        $customerId = ($customerId === null) ? 0 : $customerId;
        $this->initStripe();
        $customerManager = $this->stripePaymentsCustomerManagerFactory->getCustomerManager();
        if ($customerManager
            && method_exists($customerManager, 'retrieveByStripeID')
            && method_exists($customerManager, 'createNewStripeCustomer')
        ) {
            $stripeId = $customerManager->getStripeId();
            if ($stripeId) {
                $currentCustomer = $customerManager->retrieveByStripeID($stripeId);
            }

            if ($currentCustomer === null) {
                $currentCustomer = $customerManager->createNewStripeCustomer(
                    $firstname,
                    $lastname,
                    $email,
                    $customerId
                );
            }
        } else {
            throw new \Exception('AW SARP2: Stripe Payments Customer Manager works incorrectly');
        }
        return $currentCustomer;
    }

    /**
     * Attach payment method to a customer by payment token
     *
     * @param string $customerId
     * @param string $paymentToken
     * @return bool
     * @throws \Exception
     */
    public function attachPaymentMethodToCustomer($customerId, $paymentToken)
    {
        $result = false;
        $this->initStripe();

        $customerManager = $this->stripePaymentsCustomerManagerFactory->getCustomerManager();
        if ($customerManager
            && method_exists($customerManager, 'retrieveByStripeID')
            && method_exists($customerManager, 'addCard')
        ) {
            $customer = $customerManager->retrieveByStripeID($customerId);
            if ($customer) {
                $customerManager->addCard($paymentToken);
            }
        } else {
            throw new \Exception('AW SARP2: Stripe Payments Customer Manager works incorrectly');
        }
        return $result;
    }

    /**
     * Cancel request
     *
     * @param string $id
     * @param float|null $amount
     * @return StripeObject
     * @throws \Exception
     */
    private function cancel($id, $amount = null)
    {
        $intent = PaymentIntent::retrieve($id);

        if ($intent->status == 'requires_capture') {
            $intent->cancel();
            $result = $intent;
        } else {
            $charge = $intent->charges->data[0];
            $params = [];
            if ($amount) {
                $params['amount'] = $amount;
            }
            if (!$charge->refunded) {
                $charge->refund($params);
                $result = $charge;
            } else {
                $msg = __('This order has already been refunded in Stripe.'
                    . ' To refund from Magento, please refund it offline.');
                throw new LocalizedException($msg);
            }
        }

        return $result;
    }

    /**
     * Init stripe
     */
    private function initStripe()
    {
        $apiInfo = $this->config->getApiInfo();

        Stripe::setApiVersion('2019-03-14');
        Stripe::setApiKey($this->config->getSecretKey());
        Stripe::setAppInfo($apiInfo['module_name'], $apiInfo['module_version'], $apiInfo['module_url']);
    }
}
