<?php

namespace Abbott\Webhook\Block\Adminhtml\Webhook\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Abbott\Webhook\Model\Config\Source\Options;

class AddNewButton implements ButtonProviderInterface
{
    /**
     * @param Context $context
     */
    protected $context;
    /**
     * @var Options $webhookOptions
     */
    protected $webhookOptions;

    /**
     * Constructor
     *
     * @param Options $webhookOptions
     * @param Context $context
     */
    public function __construct(Options $webhookOptions, Context $context)
    {
        $this->webhookOptions = $webhookOptions;
        $this->context = $context;
    }

    /**
     * GetButton Data
     *
     * @return array|void
     */
    public function getButtonData()
    {
        $remainingEvents = $this->webhookOptions->getSavedEventList("savedEvents");
        if ($remainingEvents > 0) {
            return [
                'label' => __('Add new webhook'),
                'on_click' => sprintf("location.href = '%s';", $this->getNewEntryUrl()),
                'class' => 'primary',
                'sort_order' => 10
            ];
        }
    }

    /**
     * Get URL for Add New Webhook button
     *
     * @return string
     */
    public function getNewEntryUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('*/*/new', []);
    }
}
