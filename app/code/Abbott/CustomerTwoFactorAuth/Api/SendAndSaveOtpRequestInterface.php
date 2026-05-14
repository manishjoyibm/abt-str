<?php
namespace Abbott\CustomerTwoFactorAuth\Api;

interface SendAndSaveOtpRequestInterface
{
    public const EMAIL = 'email';

    /**
     * Get email id
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email id
     *
     * @param string $email
     */
    public function setEmail(string $email);
}
