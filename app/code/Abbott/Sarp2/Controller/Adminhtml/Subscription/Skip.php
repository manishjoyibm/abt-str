<?php

namespace Abbott\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Skip extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $paymentsList;

    protected $resource;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PaymentsList $paymentsList,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->paymentsList = $paymentsList;
        $this->resource = $resource;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        $newNextPaymentDate = $this->calculateNextPaymentDate($data['profile_id']);
        if ($newNextPaymentDate) {
            $payments = $this->paymentsList->getLastScheduled($data['profile_id']);
            foreach ($payments as $payment) {
                $payment->setScheduledAt($newNextPaymentDate);
                $payment->save();
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function calculateNextPaymentDate($profie_id = null)
    {
        if ($profie_id) {
            $connection = $this->resource->getConnection();
            $table = $connection->getTableName('aw_sarp2_core_schedule');
            $select = $connection->select('*')
                ->from($table)
                ->where('schedule_id = ?', $profie_id);
            $data = $connection->fetchAll($select);
            if (isset($data[0])) {
                return date('m/d/Y', strtotime("+" . $data[0]['frequency'] . " " . $data[0]['period']));
            }

        }
        return null;
    }
}
