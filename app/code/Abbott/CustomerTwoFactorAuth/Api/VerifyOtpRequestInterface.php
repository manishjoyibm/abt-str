<?php
namespace Abbott\CustomerTwoFactorAuth\Api;

interface VerifyOtpRequestInterface
{
    public const OTP = 'otp';

    public const EMAIL = 'email';

    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get OTP
     *
     * @return string
     */
    public function getOtp();

    /**
     * Set OTP
     *
     * @param string $otp
     * @return $this
     */
    public function setOtp($otp);
}
