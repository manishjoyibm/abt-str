<?php
namespace Abbott\CustomerTwoFactorAuth\Model\Api;

use Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpResponseInterface;
use Magento\Framework\DataObject;

class SendAndSaveOtpResponse extends DataObject implements SendAndSaveOtpResponseInterface
{
    const OTP = "otp";
    const KEY_SUCCESS = "success";
    const KEY_MESSAGE = "message";
    const KEY_VALUE = "value";
    const KEY_LOCK = "lock";
    const KEY_EXPIRY_MESSAGE = "expiry_message";
    /**
     * @inheritdoc
     */
    public function getOtp()
    {
        return parent::getData(self::OTP);
    }

    /**
     * @inheritdoc
     */
    public function setOtp($otp)
    {
        return $this->setData(self::OTP, $otp);
    }

    /**
     * @inheritdoc
     */
    public function getSuccess()
    {
        return $this->getData(self::KEY_SUCCESS);
    }

   /**
     * @inheritdoc
     */
    public function setSuccess($success)
     {
        return $this->setData(self::KEY_SUCCESS, $success);
    }

   /**
     * @inheritdoc
     */
    public function getMessage()
     {
        return $this->getData(self::KEY_MESSAGE);
    }

   /**
     * @inheritdoc
     */
    public function setMessage($message)
     {
        return $this->setData(self::KEY_MESSAGE, $message);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
     {
        return $this->getData(self::KEY_VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
     {
        return $this->setData(self::KEY_VALUE, $value);
    }

     /**
     * @inheritdoc
     */
    public function getLockTime()
     {
        return $this->getData(self::KEY_LOCK);
    }

    /**
     * @inheritdoc
     */
    public function setLockTime($lock)
     {
        return $this->setData(self::KEY_LOCK, $lock);
    }

    /**
     * @inheritdoc
     */
    public function getExpiryMessage()
     {
        return $this->getData(self::KEY_EXPIRY_MESSAGE);
    }

   /**
     * @inheritdoc
     */
    public function setExpiryMessage($expiry_message)
     {
        return $this->setData(self::KEY_EXPIRY_MESSAGE, $expiry_message);
    }
}
