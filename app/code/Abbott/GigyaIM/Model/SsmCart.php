<?php

namespace Abbott\GigyaIM\Model;

use Abbott\GigyaIM\Model\ResourceModel\SsmCart\Collection;
use Magento\Framework\Model\AbstractModel;
use Abbott\GigyaIM\Api\Data\SsmCartInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class SsmCart extends AbstractModel implements SsmCartInterface
{
    protected $dataObjectHelper;

    protected $ssmDataFactory;

    protected $_eventPrefix = 'ssm_shopping_cart';

    /**
     * Construct function
     *
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel\SsmCart $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                                                $context,
        Registry                                               $registry,
        ResourceModel\SsmCart                                  $resource,
        Collection $resourceCollection,
        array                                                  $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\SsmCart::class);
        parent::_construct();
    }

    /**
     * Get id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set id
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Get masked cart id
     *
     * @return string|null
     */
    public function getMaskedCartId()
    {
        return $this->getData(self::MASKED_CART_ID);
    }

    /**
     * Set masked cart id
     *
     * @param string $maskedCartId
     * @return $this
     */
    public function setMaskedCartId($maskedCartId)
    {
        return $this->setData(self::MASKED_CART_ID, $maskedCartId);
    }

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Abbott\GigyaIM\Api\Data\SsmCartExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->getDataExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Abbott\GigyaIM\Api\Data\SsmCartExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Abbott\GigyaIM\Api\Data\SsmCartExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
