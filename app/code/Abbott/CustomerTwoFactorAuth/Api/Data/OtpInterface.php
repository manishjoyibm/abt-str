<?php
namespace Abbott\CustomerTwoFactorAuth\Api\Data;

interface OtpInterface
{
    public const ENTITY_ID = "entity_id";

    public const IP_ADDRESS = "ip_address";

    public const OTP = "otp";

    public const EMAIL = "email";

    public const TIMES = "times";

    public const CREATED_AT = "created_at";

    public const UPDATED_AT = "updated_at";

    /**
     * Get id function
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Ip adress
     *
     * @return string
     */
    public function getIpAddress();

    /**
     * Get OTP
     *
     * @return string
     */
    public function getOtp();

    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get Times
     *
     * @return int
     */
    public function getTimes();

    /**
     * Get created At field value
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Get updated at field value
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Ip address
     *
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress);

    /**
     * Set OTP
     *
     * @param string $otp
     * @return $this
     */
    public function setOtp($otp);

    /**
     * Set Email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Set time
     *
     * @param int $times
     * @return $this
     */
    public function setTimes($times);

    /**
     * Set created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Update Created At
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
