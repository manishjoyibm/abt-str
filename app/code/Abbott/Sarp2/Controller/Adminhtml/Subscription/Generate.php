<?php

namespace Abbott\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Generate extends Action
{

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    private $engine;

    protected $scopeConfig;

    private $paymentsList;

    const MESSAGE = "subscription_email_setting/subscription_generate_messages/message_two";

    /**
     * @param Context $context
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        ProfileManagementInterface $profileManagement,
        Engine $engine,
        PaymentsList $paymentsList,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->profileManagement = $profileManagement;
        $this->engine = $engine;
        $this->paymentsList = $paymentsList;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getParam('profile_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $payments = $this->paymentsList->getLastScheduled($profileId);
        $todatDate = date('m/d/Y');
        $message = $this->scopeConfig->getValue(self::MESSAGE, ScopeInterface::SCOPE_STORE);
        $paymentIds = [];
        foreach ($payments as $payment) {
            $paymentIds[] = $payment->getItemId();
            $payment->setType(PaymentInterface::TYPE_ACTUAL);
            $payment->setData('payment_status',PaymentInterface::STATUS_PENDING);
            $payment->setScheduledAt($todatDate);
            $payment->save();
        }
        if ($profileId) {
            try {
                $generate = $this->engine->processPaymentsForToday($paymentIds);
                $this->messageManager->addSuccessMessage(__($message));
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while generating order.')
                );
            }
        }
        return $resultRedirect->setPath('*/*/view', ["profile_id" => $profileId]);
    }
}
