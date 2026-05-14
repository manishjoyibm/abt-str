<?php

namespace Abbott\Webhook\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;

abstract class Webhook extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Abbott_Webhook::webhook';

    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param Page $resultPage
     * @return Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Abbott'), __('Abbott'))
            ->addBreadcrumb(__('Webhook'), __('Webhook'));
        return $resultPage;
    }
}
