<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\Composite as CompositeConfigProvider;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\LayoutProcessor;

/**
 * Class Payment
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit
 */
class Payment extends Template
{
    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var LayoutProcessor
     */
    private $layoutProcessor;

    /**
     * @param Context $context
     * @param JsonSerializer $serializer
     * @param CompositeConfigProvider $configProvider
     * @param LayoutProcessor $layoutProcessor
     * @param array $data
     */
    public function __construct(
        Context $context,
        JsonSerializer $serializer,
        CompositeConfigProvider $configProvider,
        LayoutProcessor $layoutProcessor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->layoutProcessor = $layoutProcessor;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $this->jsLayout = $this->layoutProcessor->process($this->jsLayout);
        return $this->serializer->serialize($this->jsLayout);
    }

    /**
     * Retrieve serialized checkout config.
     *
     * @return bool|string
     */
    public function getSerializedCheckoutConfig()
    {
        $checkoutConfig = $this->configProvider->getConfig();
        return  $this->serializer->serialize($checkoutConfig);
    }
}
