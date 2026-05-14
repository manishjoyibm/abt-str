<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Integration\PaymentData\Braintree\Adapter;

use Aheadworks\Sarp2\PaymentData\Braintree\Adapter\Client;
use Braintree\Customer;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class ClientStub
 * @package Aheadworks\Sarp2\Test\Integration\PaymentData\Braintree\Adapter
 */
class ClientStub extends Client
{
    /**
     * Gateway token
     */
    const TOKEN = 'braintree_token';

    /**
     * {@inheritdoc}
     */
    public function createCustomer(TransferInterface $transfer)
    {
        $customerData = include __DIR__ . '/_files/customer_data.php';
        return ['object' => Customer::factory($customerData)];
    }
}
