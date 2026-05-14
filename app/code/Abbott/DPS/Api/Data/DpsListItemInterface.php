<?php

namespace Abbott\DPS\Api\Data;

/**
 * Interface DpsListItemInterface
 */
interface DpsListItemInterface
{
    /**
     *
     */
    public const ID = "entity_id";
    /**
     *
     */
    public const START_DATE = "start_date";
    /**
     *
     */
    public const END_DATE = "end_date";
    /**
     *
     */
    public const NAME = "name";
    /**
     *
     */
    public const SOURCE = "source";
    /**
     *
     */
    public const TYPE = "type";

    /**
     *
     */
    public const REFERENCE_ID = "reference_id";

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
     * Method getStartDate
     *
     * @return string
     */
    public function getStartDate(): string;

    /**
     * Method setStartDate
     *
     * @param string|null $date
     * @return $this
     */
    public function setStartDate(?string $date): static;

    /**
     * Method getEndDate
     *
     * @return string|null
     */
    public function getEndDate(): ?string;

    /**
     * Method setEndDate
     *
     * @param string|null $date
     * @return $this
     */
    public function setEndDate(?string $date): static;

    /**
     * Method getName
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Method setName
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static;

    /**
     * Method getSource
     *
     * @return string|null
     */
    public function getSource(): ?string;

    /**
     * Method setSource
     *
     * @param string|null $source
     * @return $this
     */
    public function setSource(?string $source): static;

    /**
     * Method getType
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Method setType
     *
     * @param string|null $type
     * @return $this
     */
    public function setType(?string $type): static;

    /**
     * Method getReferenceId
     *
     * @return string
     */
    public function getReferenceId(): string;

    /**
     * Method setReferenceId
     *
     * @param string $referenceId
     * @return $this
     */
    public function setReferenceId(string $referenceId): static;

    /**
     * Method getAddresses
     *
     * @return DpsListItemAddressInterface[]
     */
    public function getAddresses(): array;

    /**
     * Method setAddresses
     *
     * @param DpsListItemAddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array|null $addresses);
}
