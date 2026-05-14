<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type\Braintree;

use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\AbstractTokenRenderer;
use PayPal\Braintree\Model\Ui\PayPal\ConfigProvider;
use PayPal\Braintree\Gateway\Config\PayPal\Config as BraintreePayPalConfig;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class PayPal
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type\Braintree
 */
class PayPal extends AbstractTokenRenderer
{
    /**
     * @var BraintreePayPalConfig
     */
    private $braintreePayPalConfig;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param BraintreePayPalConfig $braintreePayPalConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        BraintreePayPalConfig $braintreePayPalConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->braintreePayPalConfig = $braintreePayPalConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function canRender($token)
    {
        return $token->getPaymentMethod() === ConfigProvider::PAYPAL_CODE;
    }

    /**
     * Retrieve email of PayPal payer
     *
     * @param array $tokenDetails
     * @return string
     */
    public function getPayerEmail($tokenDetails)
    {
        return isset($tokenDetails['payerEmail']) ? $tokenDetails['payerEmail'] : '';
    }

    /**
     * Retrieve payment type icon url
     *
     * @return string
     */
    public function getIconUrl()
    {
        $iconData = $this->braintreePayPalConfig->getPayPalIcon();
        return isset($iconData['url']) ? $iconData['url'] : '';
    }

    /**
     * Retrieve payment type icon height
     *
     * @return int
     */
    public function getIconHeight()
    {
        $iconData = $this->braintreePayPalConfig->getPayPalIcon();
        return isset($iconData['height']) ? $iconData['height'] : 0;
    }

    /**
     * Retrieve payment type icon width
     *
     * @return int
     */
    public function getIconWidth()
    {
        $iconData = $this->braintreePayPalConfig->getPayPalIcon();
        return isset($iconData['width']) ? $iconData['width'] : 0;
    }
}
