<?php

namespace Abbott\DPS\Api\Data;

/**
 * Interface DpsListItemAddressInterface
 */
interface DpsListItemAddressInterface
{
    public const ID = "id";
    public const ADDRESS = "address";
    public const CITY = "city";
    public const STATE = "state";
    public const POSTAL_CODE = "postal_code";
    public const COUNTRY = "country";
    public const PARENT_ID = "parent_id";

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Method getAddress
     *
     * @return string
     */
    public function getAddress(): string;

    /**
     * Method setAddress
     *
     * @param string $address
     * @return $this
     */
    public function setAddress(string $address): static;

    /**
     * Method getCity
     *
     * @return string
     */
    public function getCity(): string;

    /**
     * Method setCity
     *
     * @param string $city
     * @return $this
     */
    public function setCity(string $city): static;

    /**
     * Method getState
     *
     * @return string
     */
    public function getState(): string;

    /**
     * Method setState
     *
     * @param string $state
     * @return $this
     */
    public function setState(string $state): static;

    /**
     * Method getPostalCode
     *
     * @return string
     */
    public function getPostalCode(): string;

    /**
     * Method setPostalCode
     *
     * @param string $postalCode
     * @return $this
     */
    public function setPostalCode(string $postalCode): static;

    /**
     * Method getCountry
     *
     * @return string
     */
    public function getCountry(): string;

    /**
     * Method setCountry
     *
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country): static;

    /**
     * Method setParentId
     *
     * @param int $id
     * @return $this
     */
    public function setParentId(int $id): static;

    /**
     * Method getParentId
     *
     * @return int
     */
    public function getParentId(): int;
}
