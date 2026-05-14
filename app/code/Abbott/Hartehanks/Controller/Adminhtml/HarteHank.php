<?php

namespace Abbott\Hartehanks\Controller\Adminhtml;

abstract class HarteHank extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Abbott_Hartehanks::system_hartehanks';
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
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
            ->addBreadcrumb(__('Hartehank'), __('Hartehank'));
        return $resultPage;
    }
}
