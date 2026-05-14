<?php

namespace Abbott\WorkdayFeed\Controller\Adminhtml\InboundFeed;

use Abbott\WorkdayFeed\Model\InboundFeedFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;

/**
 * Delete InboundFeed action.
 */
class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Abbott_WorkdayFeed::delete';

    /**
     * @var InboundFeedFactory
     */
    protected InboundFeedFactory $inboundFeedFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        InboundFeedFactory $inboundFeedFactory
    ) {
        parent::__construct($context);
        $this->inboundFeedFactory = $inboundFeedFactory;
    }
    /**
     * Delete action
     *
     * @return Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('feed_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->inboundFeedFactory->create();
                $model->load($id);

                $title = $model->getTitle();
                $model->delete();

                // display success message
                $this->messageManager->addSuccessMessage(__('The Feed has been deleted.'));

                // go to grid
                $this->_eventManager->dispatch('adminhtml_inboundFeed_on_delete', [
                    'title' => $title,
                    'status' => 'success'
                ]);

                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_inboundFeed_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/form', ['feed_id' => $id]);
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a page to delete.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
