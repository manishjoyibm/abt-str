<?php

namespace Abbott\Chargeback\Controller\Adminhtml\Chargeback;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Abbott\Chargeback\Model\ChargebackSync;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;

class ChargebackSFTPReader extends Action implements HttpGetActionInterface
{
    /**
     * @var ChargebackSync
     */
    protected ChargebackSync $chargebackSync;

    /**
     * @param Context $context
     * @param ChargebackSync $chargebackSync
     */
    public function __construct(
        Context $context,
        ChargebackSync $chargebackSync
    ) {
        $this->chargebackSync = $chargebackSync;
        parent::__construct($context);
    }

    /**
     * Execute Method
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->chargebackSync->sftpReader();
        $this->messageManager->addSuccessMessage(__('You Processed the Chargeback Data'));
        return $resultRedirect->setPath('abbott_chargeback/chargeback/index');
    }
}
