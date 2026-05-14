<?php
namespace Abbott\PasswordHistory\Api;

use Magento\Framework\Exception\LocalizedException;

interface UsedPasswordManagementInterface
{
    /**
     * @param string $email
     * @param string $password
     * @return bool
     * @throws LocalizedException
     */
    public function validatePassword($email, $password);

    /**
     * @param string $email
     * @param string $password
     */
    public function saveUsedPassword($email, $password);
}
