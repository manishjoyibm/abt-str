<?php

namespace Abbott\MyAccount\Plugin\Controller\Manage;

use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Newsletter\Controller\Manage\Save;

class SavePlugin
{

    /**
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     *
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Construct function
     *
     * @param ManagerInterface $messageManager
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * AfterExecute function
     *
     * @param Save $subject
     * @param $result
     * @return Redirect
     */
    public function afterExecute(Save $subject, $result)
    {
        $isSubscribedParam = (boolean)$subject->getRequest()->getParam('is_subscribed', false);
        $this->messageManager->getMessages(true);
        if ($isSubscribedParam) {
            $this->messageManager->addSuccess(__('You have selected to receive emails.'));
        } else {
            $this->messageManager->addSuccess(__('You have selected not to receive emails.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
