<?php
namespace Abbott\CustomerTwoFactorAuth\Controller\LoginSecurity;

use Exception;
use Abbott\CustomerTwoFactorAuth\Helper\Data as DataHelper;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Framework\View\Result\PageFactory;
use Abbott\CustomerTwoFactorAuth\Api\OtpManagerInterface;

/**
 * Enable 2fa action controller.
 */
class EnablePost extends AbstractAccount implements HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var OtpManagerInterface
     */
    protected $otpManager;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param DataHelper $helper
     * @param OtpManagerInterface $otpManager
     * @param MessageManager $messageManager
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataHelper $helper,
        OtpManagerInterface $otpManager,
        MessageManager $messageManager,
        JsonFactory $resultJsonFactory,
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $messageManager;
        $this->otpManager = $otpManager;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Enable 2fa action handler.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $otpToken = $this->getRequest()->getPost('otp_token');
        $customer = $this->helper->getCustomerData();
        $result= $this->resultJsonFactory->create();
        $redirectUrl = $this->_url->getUrl('*/*/enable');

        if ($this->helper->isCustomerSecurityEnabled()) {
            $result->setData([
                'success' => true,
                'message' => "Two Factor Authentication has been enabled.",
                 'url' => $redirectUrl,
                'value' => 3
            ]);
            return $result;
        }
        // validate the token parameter
        if ($otpToken === null) {
            $result->setData([
                'success' => false,
                'message' => "OTP is blank",
                'value' => 3,
                'url' => $redirectUrl
            ]);
            return $result;
        }
        $customer['otp'] = $otpToken;
        $isVerified = $this->otpManager->verifyOtp($customer);
        if ($isVerified['value'] == 1) {
            $this->helper->customerSession->setData(DataHelper::ENABLING_2FA, true);
            $message= $isVerified['message'];
            $success = $isVerified['success'];
            $value = $isVerified['value'];
            $url = $redirectUrl;
        } else {
            $this->helper->setCustomerSecurity();
            $this->messageManager->addSuccessMessage(__('Two Factor Authentication has been enabled.'));
            $message= $isVerified['message'];
            $success = $isVerified['success'];
            $value = $isVerified['value'];
            $url = $this->_url->getUrl('*/*');
        }
        return $result->setData([
            'success' => $success,
            'message' => $message,
            'url' => $url,
            'value' => $value
        ]);
    }
}
