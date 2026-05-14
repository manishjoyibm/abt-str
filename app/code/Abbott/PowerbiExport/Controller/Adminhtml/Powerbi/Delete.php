<?php
namespace Abbott\PowerbiExport\Controller\Adminhtml\Powerbi;

use Magento\Framework\Controller\ResultFactory;
use Abbott\PowerbiExport\Api\PowerbiExportRepositoryInterface;

/**
 * Delete powerbi action.
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var PowerbiRepositoryInterface
     */
    private $powerbiRepository;
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param PowerbiExportRepositoryInterface $powerbiRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PowerbiExportRepositoryInterface $powerbiRepository
    ) {
        $this->powerbiRepository = $powerbiRepository;
        parent::__construct($context);
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Abbott_PowerbiExport::delete");
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $powerbiId  = (int)$this->getRequest()->getParam('entity_id');
        if (!empty($powerbiId)) {
            try {
                $this->powerbiRepository->deleteById($powerbiId);
                $this->messageManager->addSuccessMessage(__('You deleted record.'));
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('powerbi_export');
    }
}
