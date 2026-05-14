<?php

namespace Abbott\DPS\Model;

use Abbott\DPS\Api\Data\DpsListItemInterface;
use Abbott\DPS\Model\ResourceModel\DpsListItemAddress\Collection;
use Abbott\DPS\Model\ResourceModel\DpsListItemAddress\CollectionFactory as AddressCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class DpsListItem extends AbstractModel implements
    IdentityInterface,
    DpsListItemInterface
{
    /**
     *
     */
    public const CACHE_TAG = 'abbott_dps_list';

    /**
     * @var string
     */
    protected $_cacheTag = 'abbott_dps_list';

    /**
     * @var string
     */
    protected $_eventPrefix = 'abbott_dps_list';

    protected $addresses;

    /**
     * @var AddressCollectionFactory
     */
    protected AddressCollectionFactory $addressCollectionFactory;

    /**
     * DpsListItem constructor.
     * @param Context $context
     * @param Registry $registry
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AddressCollectionFactory $addressCollectionFactory,
        AbstractResource$resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->addressCollectionFactory = $addressCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\DpsListItem::class);
    }

    /**
     * Method getIdentities
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
    public function getStartDate(): string
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setStartDate(string|null $date): static
    {
        return $this->setData(self::START_DATE, $date);
    }

    /**
     * @inheritDoc
     */
    public function getEndDate(): string
    {
        return $this->getData(self::END_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setEndDate(string|null $date): static
    {
        return $this->setData(self::END_DATE, $date);
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
    public function getSource(): string
    {
        return $this->getData(self::SOURCE);
    }

    /**
     * @inheritDoc
     */
    public function setSource(string|null $source): static
    {
        return $this->setData(self::SOURCE, $source);
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setType(string|null $type): static
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getAddresses(): array
    {
        if (!$this->addresses && $this->getId()) {
            $collection = $this->addressCollectionFactory->create();
            $collection->addFieldToSelect('*');
            $collection->addFieldToFilter('parent_id', $this->getId());
            if ($collection->count() > 0) {
                $this->addresses = $collection->getItems();
            }
        }
        if (!$this->addresses) {
            $this->addresses = [];
        }
        return $this->addresses;
    }

    /**
     * @inheritDoc
     */
    public function setAddresses(array|null $addresses): void
    {
        $this->addresses = $addresses;
    }

    /**
     * @inheritDoc
     */
    public function getReferenceId(): string
    {
        return $this->getData(self::REFERENCE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReferenceId(string $referenceId): static
    {
        return $this->setData(self::REFERENCE_ID, $referenceId);
    }
}
