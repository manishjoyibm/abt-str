<?php
namespace Abbott\AbbottReport\Controller\Adminhtml\Report;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\Driver\File;

class Downloadpromoreport extends \Magento\Backend\App\Action
{

    /**
     * @var \Abbott\AbbottReport\Model\Export\Promo
     */
    protected \Abbott\AbbottReport\Model\Export\Promo $exportPromo;
    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;
    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;
    /**
     * @var File
     */
    protected File $file;

    public function __construct(
        \Magento\Backend\App\Action\Context              $context,
        \Abbott\AbbottReport\Model\Export\Promo          $exportPromo,
        FileFactory $fileFactory,
        DirectoryList                                    $directoryList,
        File                                             $file
    ) {
        $this->exportPromo = $exportPromo;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * This function is used to create promo report
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $data=$this->getRequest()->getParams();
        $filename = $this->exportPromo->exportPromoData($data);
        if ($filename!=null) {
            return $this->fileFactory->create(
                $filename,
                $this->file->fileGetContents($this->directoryList->getPath('var') . '/' . $filename),
                DirectoryList::VAR_DIR
            );
        } else {
            $this->_redirect('abbottreport/report/promo');
        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
