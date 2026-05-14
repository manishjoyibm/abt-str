<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\AbstractTokenRenderer;
use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\CreditCardIconResolver;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class AbstractCreditCardRenderer
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
abstract class AbstractCreditCardRenderer extends AbstractTokenRenderer
{
    /**
     * @var CreditCardIconResolver
     */
    protected $creditCardIconResolver;

    /**
     * @param Context $context
     * @param CreditCardIconResolver $creditCardIconResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        CreditCardIconResolver $creditCardIconResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->creditCardIconResolver = $creditCardIconResolver;
    }

    /**
     * Retrieve truncated credit card number
     *
     * @param array $tokenDetails
     * @return string
     */
    abstract public function getCreditCardNumber($tokenDetails);

    /**
     * Retrieve credit card expiration date
     *
     * @param array $tokenDetails
     * @return string
     */
    abstract public function getExpirationDate($tokenDetails);

    /**
     * Retrieve credit card type
     *
     * @param array $tokenDetails
     * @return string
     */
    abstract public function getCreditCardType($tokenDetails);

    /**
     * Retrieve credit card icon url
     *
     * @param string $creditCardType
     * @return string
     */
    public function getIconUrl($creditCardType)
    {
        $iconData = $this->creditCardIconResolver->getIconData($creditCardType);
        return isset($iconData['url']) ? $iconData['url'] : '';
    }

    /**
     * Retrieve credit card icon height
     *
     * @param string $creditCardType
     * @return int
     */
    public function getIconHeight($creditCardType)
    {
        $iconData = $this->creditCardIconResolver->getIconData($creditCardType);
        return isset($iconData['height']) ? $iconData['height'] : 0;
    }

    /**
     * Retrieve credit card icon width
     *
     * @param string $creditCardType
     * @return int
     */
    public function getIconWidth($creditCardType)
    {
        $iconData = $this->creditCardIconResolver->getIconData($creditCardType);
        return isset($iconData['width']) ? $iconData['width'] : 0;
    }
}
