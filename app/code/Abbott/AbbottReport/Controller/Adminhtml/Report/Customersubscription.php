<?php
namespace Abbott\AbbottReport\Controller\Adminhtml\Report;

use Abbott\AbbottReport\Model\Flag;

class Customersubscription extends \Magento\Reports\Controller\Adminhtml\Report\AbstractReport
{
    const CSR = 'Customer Subscription Report';
    /**
     * customer subscription report action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_showLastExecutionTime(Flag::REPORT_CUSTOMER_SUBSCRIPTION_FLAG_CODE, 'customer_subscription');

            $this->_initAction()->_setActiveMenu(
                'Abbottstore_Reports::report_customer_subscription'
            )->_addBreadcrumb(
                __(self::CSR),
                __(self::CSR)
            );
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__(self::CSR));

            $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_report_customer_view.grid');
            $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

            $this->_initReportAction([$gridBlock, $filterFormBlock]);

            $this->_view->renderLayout();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('An error occurred while showing the customer subscription report.'
                    .' Please review the log and try again.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_redirect('abbottreport/report/customersubscription/');

        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
