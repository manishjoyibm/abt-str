<?php

namespace Abbott\GlucernaOrders\Controller\Adminhtml;

abstract class Managesubscription extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Abbott_GlucernaOrders::top_level';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Abbott'), __('Abbott'))
            ->addBreadcrumb(__('Manage Subscription Plans'), __('Manage Subscription Plans'));
        return $resultPage;
    }
}
