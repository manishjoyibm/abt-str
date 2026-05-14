<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\OfflinePaymentRendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data as PaymentData;

/**
 * Class OfflinePaymentDefault
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
class OfflinePaymentDefault extends Template implements OfflinePaymentRendererInterface
{
    /**
     * @var string
     */
    private $methodCode;

    /**
     * @var PaymentData
     */
    private $paymentData;

    /**
     * @param Context $context
     * @param PaymentData $paymentData
     * @param array $data
     * @param string $methodCode
     */
    public function __construct(
        Context $context,
        PaymentData $paymentData,
        array $data = [],
        $methodCode = ''
    ) {
        parent::__construct($context, $data);
        $this->methodCode = $methodCode;
        $this->paymentData = $paymentData;
    }

    /**
     * {@inheritdoc}
     */
    public function canRender($methodCode)
    {
        return $this->methodCode === $methodCode;
    }

    public function render()
    {
        try {
            $paymentMethod = $this->paymentData->getMethodInstance($this->methodCode);
            $this->assign('paymentMethod', $paymentMethod);
            $html = $this->toHtml();
        } catch (LocalizedException $e) {
            $html = '';
        }

        return $html;
    }
}
