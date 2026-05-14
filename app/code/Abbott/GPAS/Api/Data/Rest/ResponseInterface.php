<?php


namespace Abbott\GPAS\Api\Data\Rest;


/**
 * Interface ResponseInterface
 * @package Abbott\GPAS\Api\Data\Rest
 */
interface ResponseInterface
{
    /**
     *
     */
    const RESULT = "result";
    /**
     *
     */
    const PRODUCT = "product";
    /**
     *
     */
    const LOCALISED_PRODUCT_DATA = "localisedProductData";
    /**
     *
     */
    const CODE = "code";
    /**
     *
     */
    const LOCATION = "location";
    /**
     *
     */
    const AUTHENTICATION_ATTEMPTS = "authenticationAttempts";
    /**
     *
     */
    const REASON = "reason";

    /**
     *
     */
    const RESPONSE_VALID = "valid";

    /**
     *
     */
    const PURCHASE_INFORMATION = "purchaseInformation";

    /**
     *
     */
    const HCP = "hcp";

    /**
     * @return boolean
     */
    public function isValid();

    /**
     * @return string
     */
    public function getResult();

    /**
     * @return string[]
     */
    public function getProduct();

    /**
     * @return string[]
     */
    public function getLocalisedProductData();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return int
     */
    public function getAuthenticationAttempts();

    /**
     * @return string[]
     */
    public function getLocation();

    /**
     * @return string
     */
    public function getReason();

    /**
     * @return string
     */
    public function getPurchaseInformation();

    /**
     * @return string[]
     */
    public function getHcp();

    /**
     * @param $response
     * @return $this
     */
    public function parseResponse($response);
}
