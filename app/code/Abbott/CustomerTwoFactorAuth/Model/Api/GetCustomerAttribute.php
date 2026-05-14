<?php
namespace Abbott\CustomerTwoFactorAuth\Model\Api;

use Abbott\CustomerTwoFactorAuth\Api\GetCustomerAttributeInterface;
use Magento\Framework\DataObject;

/**
 * Class GetCustomerAttribute
 */
class GetCustomerAttribute extends DataObject implements GetCustomerAttributeInterface
{
    /**
     * @return array|mixed|string|null
     */
    public function getEmail()
    {
        return parent::getData(self::EMAIL);
    }

    /**
     * @param $email
     * @return GetCustomerAttribute|string
     */
    public function setEmail($email)
    {
        return $this->setData('email', $email);
    }
    /**
     * @return array|mixed|string|null
     */
    public function getPass()
    {
        return parent::getData(self::PASS);
    }

    /**
     * @param $pass
     * @return GetCustomerAttribute|string
     */
    public function setPass($pass)
    {
        return $this->setData('pass', $pass);
    }
}
