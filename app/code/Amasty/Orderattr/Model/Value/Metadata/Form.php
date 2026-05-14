<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Value\Metadata;

use Amasty\Orderattr\Model\Attribute\RelationValidator;
use Amasty\Orderattr\Model\Config\Source\CheckoutStep;
use Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails\CollectionFactory
    as RelationDetailsCollectionFactory;
use Amasty\Orderattr\Model\Value\Metadata\Form\CollectionFilter\FilterPool;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;

/**
 * @method \Amasty\Orderattr\Model\Attribute\Attribute[] getAllowedAttributes()
 * @method \Amasty\Orderattr\Model\Attribute\Attribute[] getAttributes()
 * @method \Amasty\Orderattr\Model\Attribute\Attribute|bool getAttribute($attributeCode)
 */
class Form extends \Magento\Eav\Model\Form
{
    public const FORMAT_TO_VALIDATE_RELATIONS = 'amasty_relations';

    /**
     * Current module pathname
     *
     * @var string
     */
    protected $_moduleName = 'Amasty_Orderattr';

    /**
     * Current EAV entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = \Amasty\Orderattr\Model\ResourceModel\Entity\Entity::ENTITY_TYPE_CODE;

    /**
     * @var string
     */
    private $shippingMethod;

    /**
     * @var int
     */
    private $customerGroupId;

    /**
     * @var array
     */
    private $productIds;

    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var RelationDetailsCollectionFactory
     */
    private $attributeRelationCollectionFactory;

    /**
     * @var FilterPool
     */
    private $collectionFilterPool;

    /**
     * @var string[]
     */
    private $forbiddenAttributesCodes = [];

    /**
     * @var RelationValidator
     */
    private $relationValidator;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Eav\Model\AttributeDataFactory $attrDataFactory,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        RequestInterface $httpRequest,
        \Magento\Framework\Validator\ConfigFactory $validatorConfigFactory,
        \Amasty\Orderattr\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        RelationDetailsCollectionFactory $attributeRelationCollectionFactory,
        FilterPool $collectionFilterPool,
        RelationValidator $relationValidator = null //todo: move to not optional
    ) {
        parent::__construct(
            $storeManager,
            $eavConfig,
            $modulesReader,
            $attrDataFactory,
            $universalFactory,
            $httpRequest,
            $validatorConfigFactory
        );

        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeRelationCollectionFactory = $attributeRelationCollectionFactory;
        $this->collectionFilterPool = $collectionFilterPool;
        $this->relationValidator = $relationValidator ?? ObjectManager::getInstance()->create(
            RelationValidator::class
        );
    }

    /**
     * Return current entity instance
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getEntity()
    {
        if ($this->_entity === null) {
            $this->_entity = $this->_universalFactory->create(\Amasty\Orderattr\Model\Entity\EntityData::class);
        }

        return $this->_entity;
    }

    /**
     * Get EAV Entity Form Attribute Collection with applied filters
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @return \Amasty\Orderattr\Model\ResourceModel\Attribute\Collection
     */
    protected function _getFilteredFormAttributeCollection()
    {
        $this->_allowedAttributes = $this->_systemAttributes = [];

        $collection = $this->_getFormAttributeCollection()
            ->addAttributeGrouping()
            ->setSortOrder();

        if ($this->_ignoreInvisible) {
            if ($this->_store) {
                $collection->addStoreFilter($this->_store->getId());
            }

            if ($this->getCustomerGroupId() !== null) {
                $collection->addCustomerGroupFilter($this->getCustomerGroupId());
            }

            if ($this->getShippingMethod() !== null) {
                $collection->addShippingMethodsFilter($this->getShippingMethod());
            }

            if (!empty($this->getProductIds())) {
                $collection->addConditionsFilter($this->getProductIds());
            }

            foreach ($this->collectionFilterPool->getAll() as $filter) {
                $filter->apply($collection, $this->getEntity()->getCustomAttributes());
            }
        }

        return $collection;
    }

    /**
     * Get EAV Entity Form Attribute Collection
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @return \Amasty\Orderattr\Model\ResourceModel\Attribute\Collection
     */
    protected function _getFormAttributeCollection()
    {
        return $this->attributeCollectionFactory->create();
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * Whether the specified attribute needs to skip rendering/validation
     *
     * @param \Amasty\Orderattr\Model\Attribute\Attribute $attribute
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @return bool
     */
    protected function _isAttributeOmitted($attribute)
    {
        if ($this->_ignoreInvisible
            && (
                !$this->isAttributeVisibleForCurrentFormCode($attribute)
                || (
                    $this->getShippingMethod()
                    && !empty($attribute->getShippingMethods())
                    && !in_array($this->getShippingMethod(), $attribute->getShippingMethods())
                )
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $shippingMethod
     *
     * @return $this
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    /**
     * @param int $customerGroupId
     *
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = (int)$customerGroupId;

        return $this;
    }

    public function getProductIds(): ?array
    {
        return $this->productIds;
    }

    public function setProductIds(array $productIds): Form
    {
        $this->productIds = $productIds;

        return $this;
    }

    /**
     * Whether the specified attribute needs to skip rendering/validation
     *
     * @param \Amasty\Orderattr\Model\Attribute\Attribute $attribute
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    protected function isAttributeVisibleForCurrentFormCode($attribute)
    {
        switch ($this->getFormCode()) {
            case 'adminhtml_checkout':
            case 'adminhtml_order_view':
                return (bool)$attribute->getIsVisibleOnBack();
            case 'amasty_checkout':
                return (bool) $attribute->getIsVisibleOnFront();
            case 'amasty_checkout_shipping':
                return (bool)$attribute->getIsVisibleOnFront()
                    && in_array(
                        $attribute->getCheckoutStep(),
                        [
                            CheckoutStep::SHIPPING_STEP,
                            CheckoutStep::SHIPPING_METHODS,
                            CheckoutStep::SHIPPING_STEP_BEFORE,
                            CheckoutStep::SHIPPING_METHODS_AFTER
                        ]
                    );
            case 'amasty_checkout_virtual':
                return (bool)$attribute->getIsVisibleOnFront()
                    && in_array(
                        $attribute->getCheckoutStep(),
                        [
                            CheckoutStep::PAYMENT_STEP,
                            CheckoutStep::PAYMENT_PLACE_ORDER,
                            CheckoutStep::ORDER_SUMMARY
                        ]
                    );
            case 'frontend_order_print':
                return (bool)$attribute->getIsVisibleOnFront()
                    && $attribute->getIncludeInHtmlPrintOrder()
                    && !$attribute->getIsHiddenFromCustomer();
            case 'frontend_order_view':
                return !$attribute->getIsHiddenFromCustomer();
            case 'frontend_order_email':
                return (bool)$attribute->getIsVisibleOnFront()
                    && $attribute->isIncludeInEmail()
                    && !$attribute->getIsHiddenFromCustomer();
            case 'adminhtml_order_inline_edit':
                return (bool)$attribute->getIsVisibleOnBack() &&
                    (bool)$attribute->isShowOnGrid();
            case 'adminhtml_order_print':
                return (bool)$attribute->getIsVisibleOnBack()
                    && $attribute->getIncludeInPdf();
        }

        return (bool)$attribute->getIsVisibleOnFront() && !$attribute->getIsHiddenFromCustomer();
    }

    protected function isAdminArea()
    {
        switch ($this->getFormCode()) {
            case 'adminhtml_checkout':
            case 'adminhtml_order_view':
            case 'adminhtml_order_print':
            case 'adminhtml_order_inline_edit':
                return true;
        }
        return false;
    }

    /**
     * Restore data array to current entity
     *
     * @param array $data
     * @return $this
     */
    public function restoreData(array $data)
    {
        if ($this->_ignoreInvisible && !$this->isAdminArea()) {
            $this->modifyAvailableAttributesByData($data);
            $this->getEntity()->setForbiddenAttributeCodes($this->forbiddenAttributesCodes);
        }

        return parent::restoreData($data);
    }

    /**
     * Remove order attribute value if attribute hided by relation
     *
     * @param array $data
     */
    public function modifyAvailableAttributesByData($data)
    {
        if (empty($data)) {
            return;
        }

        foreach ($this->getAllowedAttributes() as $attribute) {
            if ($attribute->getFrontendInput() === 'html') {
                unset($this->_allowedAttributes[$attribute->getAttributeCode()]);
            }
        }

        $attributesToSave = $this->relationValidator->validateRelations($data);
        foreach (array_keys($this->getAllowedAttributes()) as $attributeCode) {
            if (array_key_exists($attributeCode, $attributesToSave) && !$attributesToSave[$attributeCode]) {
                unset($this->_allowedAttributes[$attributeCode]);
                $this->forbiddenAttributesCodes[] = $attributeCode;
            }
        }
    }

    /**
     * Set whether invisible attributes should be ignored.
     *
     * @param bool $ignoreInvisible
     *
     * @return $this
     */
    public function setInvisibleIgnored($ignoreInvisible)
    {
        $this->_ignoreInvisible = $ignoreInvisible;

        return $this;
    }
}
