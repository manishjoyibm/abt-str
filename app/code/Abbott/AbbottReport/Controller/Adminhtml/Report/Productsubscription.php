<?php
namespace Abbott\AbbottReport\Controller\Adminhtml\Report;

use Abbott\AbbottReport\Model\Flag;

class Productsubscription extends \Magento\Reports\Controller\Adminhtml\Report\AbstractReport
{
    const PRSR = 'Product Subscription Report';
    /**
     * product subscription report action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_showLastExecutionTime(Flag::REPORT_PRODUCT_SUBSCRIPTION_FLAG_CODE, 'product_subscription');

            $this->_initAction()->_setActiveMenu(
                'Abbottstore_Reports::report_product_subscription'
            )->_addBreadcrumb(
                __(self::PRSR),
                __(self::PRSR)
            );
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__(self::PRSR));

            $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_report_product_view.grid');
            $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

            $this->_initReportAction([$gridBlock, $filterFormBlock]);

            $this->_view->renderLayout();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('An error occurred while showing the product subscription report.'
                    .' Please review the log and try again.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_redirect('abbottreport/report/productsubscription/');

        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
