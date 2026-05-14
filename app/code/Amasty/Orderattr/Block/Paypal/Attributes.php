<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Block\Paypal;

use Amasty\Orderattr\Model\Attribute\Frontend\CollectionProvider;
use Amasty\Orderattr\Model\Attribute\InputType\InputTypeProvider;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Attributes extends Template
{
    /**
     * @var null|string
     */
    private $attributesJsLayout;

    /**
     * @var string
     */
    protected $_template = 'Amasty_Orderattr::paypal/attributes.phtml';

    /**
     * @var CollectionProvider
     */
    private $collectionProvider;

    /**
     * @var InputTypeProvider
     */
    private $inputTypeProvider;

    /**
     * @var CompositeConfigProvider
     */
    private $checkoutConfigProvider;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        CollectionProvider $collectionProvider,
        InputTypeProvider $inputTypeProvider,
        CompositeConfigProvider $checkoutConfigProvider,
        SerializerInterface $serializer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionProvider = $collectionProvider;
        $this->inputTypeProvider = $inputTypeProvider;
        $this->checkoutConfigProvider = $checkoutConfigProvider;
        $this->serializer = $serializer;
    }

    public function getJsLayout(): string
    {
        if ($this->attributesJsLayout === null) {
            $this->attributesJsLayout = [];
            if ($attributes = $this->collectionProvider->getAttributes()) {
                $this->attributesJsLayout['components'] = [
                    'amorder_attributes_fields' => [
                        'component' => 'Amasty_Orderattr/js/view/order-attributes',
                        'name' => 'amorder_attributes_fields',
                        'scope' => 'amorder_attributes_fields',
                        'amScope' => 'amorder_attributes_fields',
                        'template' => 'Amasty_Orderattr/order-attributes-div',
                        'children' => $this->inputTypeProvider->getFrontendElements(
                            $attributes,
                            'amastyCheckoutProvider',
                            'amorder_attributes_fields'
                        )
                    ]
                ];
            }
            $this->attributesJsLayout['components']['amastyCheckoutProvider'] = ['component' => 'uiComponent'];
        }

        return $this->serializer->serialize($this->attributesJsLayout);
    }

    public function getCheckoutConfig(): string
    {
        return $this->serializer->serialize($this->checkoutConfigProvider->getConfig());
    }
}
