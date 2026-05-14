<?php
namespace Abbott\CustomerTwoFactorAuth\Api\Data;

interface SendAndSaveOtpResponseInterface{

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
    
    /**
     * success flag
     * 
     * @return bool
     */
    public function getSuccess();

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success);

    /**
     * Message String
     * 
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

      /**
     * Value/ Code /Custom data 
     * 
     * @return string| null
     */
    public function getAttempt();

    /**
     * @param string|null $count 
     * @return $this
     */
    public function setAttempt($count);

      /**
     * Value/ Code /Custom data 
     * 
     * @return string| null
     */
    public function getLimit();

    /**
     * @param string|null $limit 
     * @return $this
     */
    public function setLimit($limit);

      /**
     * Value/ Code /Custom data 
     * 
     * @return string| null
     */
    public function getExpireTimerValue();

    /**
     * @param string|null $limit 
     * @return $this
     */
    public function setExpireTimerValue($timer);

/**
     * Value/ Code /Custom data 
     * 
     * @return string| null
     */
    public function getValue();

    /**
     * @param string|null $value 
     * @return $this
     */
    public function setValue($value);

    /**
     * Lock /Custom data 
     * 
     * @return int| null
     */
    public function getLockTime();

    /**
     * @param int|null $lock 
     * @return $this
     */
    public function setLockTime($lock);


     /**
     * Expiry Message String
     * 
     * @return string
     */
    public function getExpiryMessage();

    /**
     * @param string $expiry_message
     * @return $this
     */
    public function setExpiryMessage($expiry_message);

}