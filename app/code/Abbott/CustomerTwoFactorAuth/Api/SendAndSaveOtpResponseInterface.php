<?php
namespace Abbott\CustomerTwoFactorAuth\Api;

interface SendAndSaveOtpResponseInterface
{
    public const OTP = 'otp';

    /**
     * Get otp
     *
     * @return string|null
     */
    public function getOtp();

    /**
     * Set otp
     *
     * @param string $otp
     * @return $this
     */
    public function setOtp($otp);
}
