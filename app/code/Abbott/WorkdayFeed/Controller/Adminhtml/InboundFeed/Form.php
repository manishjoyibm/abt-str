<?php

namespace Abbott\WorkdayFeed\Controller\Adminhtml\InboundFeed;

use Abbott\WorkdayFeed\Model\InboundFeedFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Edit InboundFeed action.
 */
class Form extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Abbott_WorkdayFeed::save';
    public const NEWFEED = 'New Feed';
    public const FEEDID = 'feed_id';


    /**
     * Core registry
     *
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var InboundFeedFactory
     */
    protected InboundFeedFactory $inboundFeedFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param InboundFeedFactory $inboundFeedFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        InboundFeedFactory $inboundFeedFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->inboundFeedFactory = $inboundFeedFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction(): Page
    {
        // load layout, set active menu and breadcrumbs
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Abbott_WorkdayFeed::workdayfeed_inboundfeed')
            ->addBreadcrumb(__('WORKDAYFEED'), __('WORKDAYFEED'))
            ->addBreadcrumb(__('Manage Feed'), __('Manage Feed'));
        return $resultPage;
    }

    /**
     * Execute action based on request and return result
     *
     * @return Page|Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(self::FEEDID);
        $model = $this->inboundFeedFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getFeedId()) {
                $this->messageManager->addErrorMessage(__('This page no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('workdayfeed_inboundfeed', $model);

        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Form Page') : (self::NEWFEED),
            $id ? __('Form Page') : (self::NEWFEED)
        );
        $resultPage->getConfig()->getTitle()->prepend(__('InboundFeed'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getFeedId() ? $model->getFileName() : (self::NEWFEED));

        return $resultPage;
    }
}
