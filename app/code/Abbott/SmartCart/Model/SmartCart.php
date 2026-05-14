<?php


namespace Abbott\SmartCart\Model;

use Abbott\SmartCart\Api\Data\SmartCartInterface;
use Abbott\SmartCart\Model\ResourceModel\SmartCart as SmartCartResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class SmartCart extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    SmartCartInterface
{

    /**
     * string
     */
    public const CACHE_TAG = 'abbott_smartcart_smartcart';

    /**
     * @var string
     */
    protected $_cacheTag = 'abbott_smartcart_smartcart';

    /**
     * @var string
     */
    protected $_eventPrefix = 'abbott_smartcart_smartcart';
    /**
     * @var Json
     */
    private $json;

    /**
     * SmartCart constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Json $json
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Json $json,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->json = $json;
    }

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SmartCartResource::class);
    }

    /**
     * GetIdentities
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * GetCode
     *
     * @return array|mixed|string|null
     */
    public function getCode()
    {
        return $this->getData(self::ATTR_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode($code)
    {
        return $this->setData(self::ATTR_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getProducts()
    {
        $products = null;
        if ($data = $this->getData(self::ATTR_PRODUCTS)) {
            $products = $this->json->unserialize($data);
        }
        return $products;
    }

    /**
     * @inheritDoc
     */
    public function setProducts($products)
    {
        if (is_array($products)) {
            $products = $this->json->serialize($products);
        }

        return $this->setData(self::ATTR_PRODUCTS, $products);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountRuleId()
    {
        return $this->getData(self::ATTR_DISCOUNT_RULE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountRuleId($id)
    {
        return $this->setData(self::ATTR_DISCOUNT_RULE_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return $this->getData(self::ATTR_IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setIsActive($active)
    {
        return $this->setData(self::ATTR_IS_ACTIVE, $active);
    }

    /**
     * GetIsActive
     *
     * @return array|bool|mixed|null
     */
    public function getIsActive()
    {
        return $this->getData(self::ATTR_IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::ATTR_CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($date)
    {
        return $this->setData(self::ATTR_CREATED_AT, $date);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::ATTR_UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($date)
    {
        return $this->setData(self::ATTR_UPDATED_AT, $date);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->getData(self::ATTR_STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($id)
    {
        return $this->setData(self::ATTR_STORE_ID, $id);
    }
}
