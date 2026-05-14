<?php
namespace Abbott\AbbottReport\Controller\Adminhtml\Report;

use Abbott\AbbottReport\Block\Adminhtml\Report\Product\View\Grid;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportSubscriptionProductExcel extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export shipping report grid to Excel XML format.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'product_subscription.xml';
        $grid = $this->_view->getLayout()->createBlock(Grid::class);
        $this->_initReportAction($grid);

        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
