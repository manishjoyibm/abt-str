<?php
namespace Abbott\AbbottReport\Controller\Adminhtml\Report;

use Abbott\AbbottReport\Block\Adminhtml\Report\Customer\View\Grid;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportSubscriptionCustomerCsv extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export subscription report grid to CSV format.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'customer_subscription.csv';
        $grid = $this->_view->getLayout()->createBlock(Grid::class);
        $this->_initReportAction($grid);

        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
