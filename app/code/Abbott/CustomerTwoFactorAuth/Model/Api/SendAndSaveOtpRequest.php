<?php
namespace Abbott\CustomerTwoFactorAuth\Model\Api;

use Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpRequestInterface;
use Magento\Framework\DataObject;

/**
 * Class SendAndSaveOtpRequest
 */
class SendAndSaveOtpRequest extends DataObject implements SendAndSaveOtpRequestInterface
{
    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return parent::getData(self::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }
}
