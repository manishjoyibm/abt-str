<?php
namespace Abbott\MetabolicOrdering\Controller\Adminhtml\Metabolic;

use Magento\Framework\Controller\ResultFactory;
use Abbott\MetabolicOrdering\Api\MetabolicOrderingRepositoryInterface;

/**
 * Delete metabolic action.
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var MetabolicRepositoryInterface
     */
    private $metabolicRepository;
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param MetabolicOrderingRepositoryInterface $metabolicRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        MetabolicOrderingRepositoryInterface $metabolicRepository
    ) {
        $this->metabolicRepository = $metabolicRepository;
        parent::__construct($context);
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Abbott_MetabolicOrdering::delete");
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $metabolicId  = (int)$this->getRequest()->getParam('entity_id');
        if (!empty($metabolicId)) {
            try {
                $this->metabolicRepository->deleteById($metabolicId);
                $this->messageManager->addSuccessMessage(__('You deleted record.'));
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('metabolic_ordering');
    }
}
