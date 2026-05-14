<?php
namespace Abbott\CustomerTwoFactorAuth\Api;

interface SendMessageInterface
{
    /**
     * Send otp
     *
     * @param string $otp
     * @return $this
     */
    public function sendOtpEmail($otp);
}
