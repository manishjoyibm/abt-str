<?php
namespace Abbott\CustomerTwoFactorAuth\Api;

interface GetCustomerAttributeInterface
{
    public const EMAIL = 'email';

    public const PASS = 'pass';
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
     * @return void
     */
    public function setEmail(string $email);
    /**
     * Get pass 
     *
     * @return string
     */
    public function getPass();

    /**
     * Set pass
     *
     * @param string $pass
     * @return void
     */
    public function setPass(string $pass);
}
