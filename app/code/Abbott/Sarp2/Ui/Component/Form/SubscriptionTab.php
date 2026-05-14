<?php

namespace Abbott\Sarp2\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Class Fieldset
 * @package Custom\Custom\Ui\Component\Form
 */
class SubscriptionTab extends Fieldset implements TabInterface
{
    /**
     * SubscriptionTab constructor.
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */

    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->context = $context;

        parent::__construct($context, $components, $data);
    }

    public function canShowTab()
    {
        $customerId = $this->context->getRequestParam('id');
        return (bool)$customerId;
    }

    public function prepare()
    {
        if (!$this->canShowTab()) {
            parent::prepare();
            return;
        }
    }


    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return '';
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        false;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        false;
    }
}
