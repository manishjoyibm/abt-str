<?php

namespace Abbott\Chargeback\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;

abstract class Chargeback extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Abbott_Chargeback::Chargeback';

    /**
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
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
    public function initPage(Page $resultPage): Page
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Abbott'), __('Abbott'))
            ->addBreadcrumb(__('Chargeback Integration'), __('Chargeback Integration'));
        return $resultPage;
    }
}
