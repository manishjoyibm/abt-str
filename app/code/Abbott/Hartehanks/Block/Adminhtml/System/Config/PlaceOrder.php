<?php

namespace Abbott\Hartehanks\Block\Adminhtml\System\Config;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;

class PlaceOrder implements ButtonProviderInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Context
     */
    private $context;

    /**
     * CustomButton constructor.
     *
     * @param AuthorizationInterface $authorization
     * @param Context $context
     */
    public function __construct(
        AuthorizationInterface $authorization,
        Context $context
    ) {
        $this->authorization = $authorization;
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Place Order'),
            'on_click' => 'deleteConfirm(\'' . __(
                'Are you sure, want send HH Place order?'
            ) . '\', \'' . $this->getBackUrl() . '\')',
            'class' => 'primary',
            'sort_order' => 10
        ];
    }

    public function getBackUrl()
    {
        return $this->context->getUrlBuilder()->getUrl(
            'abbott_hartehanks/hhplaceorder/placeorder',
            ['_current' => true]
        );
    }
}
