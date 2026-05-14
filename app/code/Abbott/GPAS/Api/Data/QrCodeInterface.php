<?php


namespace Abbott\GPAS\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface QrCodeInterface
 * @package Abbott\GPAS\API\Data
 */
interface QrCodeInterface extends ExtensibleDataInterface
{
    /**
     *
     */
    const ATTR_ID = "entity_id";
    /**
     *
     */
    const ATTR_CODE = "code";
    /**
     *
     */
    const ATTR_IP = "ip";
    /**
     *
     */
    const ATTR_LAT = "lat";
    /**
     *
     */
    const ATTR_LONG = "long";

    /**
     *
     */
    const CREATED_AT = "created_at";

    /**
     *
     */
    const UPDATED_AT = "updated_at";

    /**
     *
     */
    const IS_REDEEMED = "is_redeemed";

    /**
     *
     */
    const ADDITIONAL_INFORMATION = "additional_information";

    /**
     * @return int
     */
    public function getId();


    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getIp();

    /**
     * @param string $ip
     * @return $this
     */
    public function setIp($ip);

    /**
     * @return float
     */
    public function getLat();

    /**
     * @param float $lat
     * @return $this
     */
    public function setLat($lat);

    /**
     * @return float
     */
    public function getLong();

    /**
     * @param float $long
     * @return $this
     */
    public function setLong($long);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $date
     * @return $this
     */
    public function setCreatedAt($date);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $date
     * @return $this
     */
    public function setUpdatedAt($date);

    /**
     * @return boolean
     */
    public function getIsRedeemed();

    /**
     * @param boolean $redeemed
     * @return $this
     */
    public function setIsRedeemed($redeemed);

    /**
     * Additional information is not persisted in data model. It is only used to pass through information from
     * API response.
     * @param $additionalInformation
     * @return $this
     */
    public function setAdditionalInformation($additionalInformation);

    /**
     * @return mixed
     */
    public function getAdditionalInformation();

}
