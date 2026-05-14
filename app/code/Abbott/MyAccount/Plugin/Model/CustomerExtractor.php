<?php

namespace Abbott\MyAccount\Plugin\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class CustomerExtractor
{
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupManagementInterface
     */
    protected $customerGroupManagement;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Construct function
     *
     * @param Metadata\FormFactory $formFactory
     * @param CustomerInterfaceFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param GroupManagementInterface $customerGroupManagement
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        CustomerInterfaceFactory $customerFactory,
        StoreManagerInterface $storeManager,
        GroupManagementInterface $customerGroupManagement,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->formFactory = $formFactory;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * AroundExtract function
     *
     * @param \Magento\Customer\Model\CustomerExtractor $subject
     * @param callable $proceed
     * @param $formCode
     * @param RequestInterface $request
     * @param array $attributeValues
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundExtract(
        \Magento\Customer\Model\CustomerExtractor $subject,
        callable $proceed,
        $formCode,
        RequestInterface $request,
        array $attributeValues = []
    ) {
        $customerForm = $this->formFactory->create(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $formCode,
            $attributeValues
        );
        $customerData = $customerForm->extractData($request);
        $customerData = $customerForm->compactData($customerData);
        $isGroupIdEmpty = !isset($customerData['group_id']);
        $customerDataObject = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerData,
            CustomerInterface::class
        );
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();
        if ($isGroupIdEmpty) {
            $customerDataObject->setGroupId(
                $this->customerGroupManagement->getDefaultGroup($storeId)->getId()
            );
        }
        $customerDataObject->setWebsiteId($store->getWebsiteId());
        $customerDataObject->setStoreId($storeId);
        return $customerDataObject;
    }
}
