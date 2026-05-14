<?php
namespace Abbott\CustomerTwoFactorAuth\Model;

use Abbott\CustomerTwoFactorAuth\Api\GenerateOtpInterface;
use Magento\Framework\Math\Random as MathRandom;

class GenerateOtp implements GenerateOtpInterface
{
    /**
     * @var MathRandom
     */
    private $mathRandom;

    /**
     * @param MathRandom $mathRandom
     */
    public function __construct(
        MathRandom $mathRandom
    ) {
        $this->mathRandom = $mathRandom;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        return $this->mathRandom->getRandomString(1, self::CHARS_DIGITS_WITHOUT_ZERO)
            .$this->mathRandom->getRandomString(self::OTP_LENGTH-1, self::CHARS_DIGITS);
    }
}
