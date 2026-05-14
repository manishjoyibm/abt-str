<?php
namespace Abbott\CustomerTwoFactorAuth\Api;

interface SendOtpInterface
{
    /**
     * Execute function
     *
     * @return bool
     */
    public function execute();
}
