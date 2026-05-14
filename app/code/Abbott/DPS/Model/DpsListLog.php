<?php


namespace Abbott\DPS\Model;

use Abbott\DPS\Api\Data\DpsListLogInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class DpsListLog extends AbstractModel implements
    IdentityInterface,
    DpsListLogInterface
{

    /**
     *
     */
    public const CACHE_TAG = 'abbott_dps_list_log';

    /**
     * @var string
     */
    protected $_cacheTag = 'abbott_dps_list_log';

    /**
     * @var string
     */
    protected $_eventPrefix = 'abbott_dps_list_log';

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\DpsListLog::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Method getDefaultValues
     *
     * @return array
     */
    public function getDefaultValues(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $date): static
    {
        return $this->setData(self::CREATED_AT, $date);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): static
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getAddress(): string
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setAddress(string $address): static
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @inheritDoc
     */
    public function getCity(): string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritDoc
     */
    public function setCity(string $city): static
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritDoc
     */
    public function getState(): string
    {
        return $this->getData(self::STATE);
    }

    /**
     * @inheritDoc
     */
    public function setState(string $state): static
    {
        return $this->setData(self::STATE, $state);
    }

    /**
     * @inheritDoc
     */
    public function getPostalCode(): string
    {
        return $this->getData(self::POSTAL_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setPostalCode(string $postalCode): static
    {
        return $this->setData(self::POSTAL_CODE, $postalCode);
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): string
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @inheritDoc
     */
    public function setCountry(string $country): static
    {
        return $this->setData(self::COUNTRY, $country);
    }
}
