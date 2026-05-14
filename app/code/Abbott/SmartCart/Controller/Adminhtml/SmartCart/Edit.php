<?php

namespace Abbott\SmartCart\Controller\Adminhtml\SmartCart;

use Abbott\SmartCart\Model\SmartCart;
use Magento\Backend\App\Action;
use Exception;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Phrase;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Abbott\SmartCart\Model\SmartCartFactory as SmartCartFactory;
use Magento\Framework\Model\AbstractModel;

class Edit extends Action
{
    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var SmartCartFactory
     */
    private $smartCartFactory;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param SmartCartFactory $smartCartFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        SmartCartFactory $smartCartFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->smartCartFactory = $smartCartFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Page|Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var AbstractModel $model */
        if ($id) {
            try {
                $model = $this->loadEditData($id);
            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage(__(
                    'This ' . strtolower($this->getEntityTitle()) . ' no longer exists.'
                ));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->createEditData();
        }
        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->coreRegistry->register("smartcart", $model);
        /** @var Page $resultPage */
        $resultPage = $this->initAction();
        $newTitle = __('New') . ' ' . $this->getEntityTitle();
        $editTitle = __('Edit') . ' ' . $this->getEntityTitle();
        $resultPage->addBreadcrumb(
            $id ? $editTitle : $newTitle,
            $id ? $editTitle : $newTitle
        );
        $resultPage->getConfig()
            ->getTitle()
            ->prepend($model->getId() ? $editTitle . ': ' . $model->getCode() : $newTitle);
        return $resultPage;
    }

    /**
     * LoadEditData
     *
     * @param $id
     * @return SmartCart
     */
    protected function loadEditData($id)
    {
        return $this->smartCartFactory->create()->load($id);
    }

    /**
     * CreateEditData
     *
     * @return AbstractModel|Object
     */
    protected function createEditData()
    {
        return $this->smartCartFactory->create();
    }

    /**
     * GetEntityTitle
     *
     * @return Phrase
     */
    protected function getEntityTitle()
    {
        return __("SmartCart");
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function initAction()
    {
        $manageTitle = __('Manage') . ' ' . $this->getEntityTitle();
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb($this->getEntityTitle(), $this->getEntityTitle());
        $resultPage->addBreadcrumb($manageTitle, $manageTitle);
        return $resultPage;
    }
}
