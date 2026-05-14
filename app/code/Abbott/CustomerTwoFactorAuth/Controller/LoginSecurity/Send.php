<?php
namespace Abbott\CustomerTwoFactorAuth\Controller\LoginSecurity;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Abbott\CustomerTwoFactorAuth\Api\OtpManagerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Abbott\CustomerTwoFactorAuth\Helper\Data as DataHelper;

class Send extends AbstractAccount implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var OtpManagerInterface
     */
    protected $otpManager;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param OtpManagerInterface $otpManager
     * @param DataHelper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OtpManagerInterface $otpManager,
        DataHelper $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->otpManager = $otpManager;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result= $this->resultJsonFactory->create();
        $sendAndSaveOtpRequest = $this->helper->getCustomerData();
        $data = $this->otpManager->sendAndSaveOtp($sendAndSaveOtpRequest);
        if (isset($data['success'])) {
            $message= $data['message'];
            $success = $data['success'];
            $value = $data['value'];
        } else {
            $message= 'A one-time auth code has been sent to your registered email';
            $success = true;
            $value = 1;
        }
        return $result->setData([
            'success' => $success,
            'message' => $message,
            'value' =>$value
        ]);
    }
}
