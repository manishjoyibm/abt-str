<?php


namespace Abbott\Sarp2\Api\Data;


/**
 * Interface PaymentChangeLogInterface
 * @package Abbott\Sarp2\Api\Data
 */
interface PaymentChangeLogInterface
{

    const ENTITY_ID = 'entity_id';
    const PROFILE_ID = 'profile_id';
    const CUSTOMER_ID = 'customer_id';
    const TOKEN = 'token';
    const CREATED_AT = 'created_at';
    const HAS_FAILED = 'has_failed';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getProfileId();

    /**
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getToken();

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return boolean
     */
    public function getHasFailed();

    /**
     * @param boolean $failed
     * @return $this
     */
    public function setHasFailed($failed);


}
