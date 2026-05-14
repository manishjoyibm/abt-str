<?php
namespace Abbott\CustomerTwoFactorAuth\Api;

use Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpRequestInterface;
use Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpResponseInterface;
use Abbott\CustomerTwoFactorAuth\Api\VerifyOtpRequestInterface;
use Abbott\CustomerTwoFactorAuth\Api\VerifyOtpResponseInterface;
use Abbott\CustomerTwoFactorAuth\Api\GetCustomerAttributeInterface;

interface OtpManagerInterface
{
    /**
     * To send and save OTP to customer
     *
     * @param \Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpRequestInterface $request
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\SendAndSaveOtpResponseInterface
     */
    public function sendAndSaveOtp(\Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpRequestInterface $request);

    /**
     * To verify OTP
     *
      * @param VerifyOtpRequestInterface $request
     * @return boolean
     */
      public function verifyOtp($request);

    /**
     * To get customer attribute
     *
     * @param GetCustomerAttributeInterface $request
     * @return boolean
     */
    public function getCustomerAttribute($request);
}
