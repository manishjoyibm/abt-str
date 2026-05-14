<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment;

/**
 * Class LayoutProcessor
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment
 */
class LayoutProcessor
{
    /**
     * @var DefinitionFetcher
     */
    private $definitionFetcher;

    /**
     * @param DefinitionFetcher $definitionFetcher
     */
    public function __construct(DefinitionFetcher $definitionFetcher)
    {
        $this->definitionFetcher = $definitionFetcher;
    }

    /**
     * @inheritdoc
     */
    public function process($jsLayout)
    {
        if (isset($jsLayout['components']['payment']['children']['renders']['children'])
        ) {
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'braintree'
            );
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'aw_bambora_apac'
            );
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'stripe_payments'
            );
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'authorizenet_acceptjs'
            );
            $this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'offline-payments'
            );
            //Vault is disabled for now
            /*$this->addPaymentMethodRender(
                $jsLayout['components']['payment']['children']['renders']['children'],
                'vault'
            );*/
        }
        return $jsLayout;
    }

    /**
     * Add payment methods renders definitions
     *
     * @param array $layout
     * @param string $paymentMethod
     * @return void
     */
    private function addPaymentMethodRender(array &$layout, $paymentMethod)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="steps"]/item[@name="children"]/item[@name="billing-step"]'
            . '/item[@name="children"]/item[@name="payment"]/item[@name="children"]'
            . '/item[@name="renders"]/item[@name="children"]/item[@name="' . $paymentMethod . '"]';
        $definitions = $this->definitionFetcher->fetchArgs('checkout_index_index', $path);
        if (!empty($definitions)) {
            $paymentDefinition = [$paymentMethod => $definitions];
            $layout = array_merge($layout, $paymentDefinition);
        }
    }
}
