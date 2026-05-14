<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\Adapter;

use Braintree\Configuration;
use Braintree\Customer;
use Braintree\Result\Error;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class Client
 * @package Aheadworks\Sarp2\PaymentData\Braintree\Adapter
 */
class Client
{
    /**
     * @var array
     */
    private $applyConfigMap = [
        'merchant_id' => 'merchantId',
        'public_key' => 'publicKey',
        'private_key' => 'privateKey',
        'environment' => 'environment'
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($this->applyConfigMap as $key => $setter) {
            $value = isset($config[$key]) ? $config[$key] : null;
            Configuration::$setter($value);
        }
    }

    /**
     * Create customer
     *
     * @param TransferInterface $transfer
     * @return Customer
     * @throws ClientException
     */
    public function createCustomer(TransferInterface $transfer)
    {
        return $this->request($transfer, 'performCreateCustomer');
    }

    /**
     * Perform request
     *
     * @param TransferInterface $transfer
     * @param string $method
     * @return array
     * @throws ClientException
     */
    private function request(TransferInterface $transfer, $method)
    {
        $response['object'] = [];
        try {
            $response['object'] = $this->$method($transfer);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            throw new ClientException($message);
        }
        return $response;
    }

    /**
     * Perform create customer request
     *
     * @param TransferInterface $transfer
     * @return Customer
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function performCreateCustomer(TransferInterface $transfer)
    {
        $data = $transfer->getBody();
        $result = Customer::create($data);
        if ($result instanceof Error) {
            throw new \Exception($result->message);
        } else {
            return $result->customer;
        }
    }
}
