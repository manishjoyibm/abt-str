<?php
namespace Abbott\CustomerTwoFactorAuth\Model;

use Abbott\CustomerTwoFactorAuth\Api\SendMessageInterface;
use Abbott\CustomerTwoFactorAuth\Helper\Data as DataHelper;
use Abbott\CustomerTwoFactorAuth\Helper\Email as EmailHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Mail\Template\TransportBuilder;

class SendMessage implements SendMessageInterface
{
    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @param DataHelper $helper
     * @param EmailHelper $emailHelper
     */
    public function __construct(
        DataHelper $helper,
        EmailHelper $emailHelper
    ) {
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
    }

    /**
     * @param $otp
     * @return bool
     */
    public function sendOtpEmail($otp)
    {
        if (!$this->helper->isModuleEnabled()) {
            return false;
        }

        $senderName = $this->helper->getStoreName();
        $senderEmail = $this->helper->getStoreEmail();
        $otpTemplateId = $this->helper->getEmailTemplate();

        return $this->emailHelper->sendEmail(
            $senderName,
            $senderEmail,
            $otp->getOtp(),
            $otp->getEmail(),
            $otpTemplateId
        );
    }
}
