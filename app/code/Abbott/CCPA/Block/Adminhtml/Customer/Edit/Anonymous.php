<?php

namespace Abbott\CCPA\Block\Adminhtml\Customer\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Make customer as anonymous
 */
class Anonymous extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AuthorizationInterface
     */
    private AuthorizationInterface $authorization;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Context                $context,
        Registry               $registry,
        AuthorizationInterface $authorization
    ) {
        parent::__construct($context, $registry);
        $this->authorization = $authorization;
    }

    /**
     * Get Button Data
     *
     * @return mixed[]
     */
    public function getButtonData()
    {
        $data = [];

        $canDeactivate = $this->authorization->isAllowed('Abbott_CCPA::deactivate_button');

        if ($canDeactivate) {
            $data = [
                'label' => __('Deactivate Customer'),
                'class' => 'login login-button',
                'on_click' => 'deleteConfirm(\''
                    . __('Are you sure you want to deactivate this customer?')
                    . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 65,
            ];
        }
        return $data;
    }

    /**
     * Get Delete Url
     *
     * @return string
     */
    public function getDeleteUrl(): string
    {
        $customerId = $this->getCustomerId();
        return $this->getUrl('ccpa/index/index', ['customer_id' => $customerId]);
    }
}
