<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\ViewModel\Product\Braintree\PayPal;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class ButtonAdapter
 * @package Aheadworks\Sarp2\ViewModel\Product\Braintree\PayPal
 */
class ButtonAdapter implements ArgumentInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param RequestInterface $request
     * @param IsSubscription $isSubscriptionChecker
     * @param Json $jsonSerializer
     */
    public function __construct(
        RequestInterface $request,
        IsSubscription $isSubscriptionChecker,
        Json $jsonSerializer
    ) {
        $this->request = $request;
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Get JSON config
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $productId = (int) $this->request->getParam('id');
        return $this->jsonSerializer->serialize(
            ['isSubscription' => $productId && $this->isSubscriptionChecker->checkById($productId)]
        );
    }
}
