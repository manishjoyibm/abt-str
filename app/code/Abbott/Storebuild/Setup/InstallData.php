<?php

namespace Abbott\Storebuild\Setup;

/**
 * Class InstallData
 * @package Abbott\Storebuild\Setup
 */

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var GroupFactory
     */
    private $groupFactory;
    /**
     * @var Group
     */
    private $groupResourceModel;
    /**
     * @var StoreFactory
     */
    private $storeFactory;
    /**
     * @var Store
     */
    private $storeResourceModel;
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var Website
     */
    private $websiteResourceModel;
    /**
     * @var $categoryFactory
     */
    private $categoryFactory;

    /**
     * InstallData constructor.
     * @param WebsiteFactory $websiteFactory
     * @param Website $websiteResourceModel
     * @param Store $storeResourceModel
     * @param Group $groupResourceModel
     * @param StoreFactory $storeFactory
     * @param GroupFactory $groupFactory
     * @param ManagerInterface $eventManager
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        Group $groupResourceModel,
        GroupFactory $groupFactory,
        ManagerInterface $eventManager,
        Store $storeResourceModel,
        StoreFactory $storeFactory,
        Website $websiteResourceModel,
        WebsiteFactory $websiteFactory,
        CategoryFactory $categoryFactory
    ) {
        $this->eventManager = $eventManager;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @param  ModuleDataSetupInterface $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $categoryData = [
            'name' => 'Category Pedialyte Website Store',
            'is_active' => '1'
        ];


        $default_attributes = [
            [
                'website_code' => 'pedialyte_website',
                'website_name' => 'Pedialyte Website',
                'group_code' => 'pedialyte_store_group',
                'group_name' => 'Pedialyte store group',
                'root_category_id' => $this->createOrUpdateRootCategory($categoryData),
                'store_code' => 'pedialyte',
                'store_name' => 'Pedialyte',
                'is_active' => '1'
            ]
        ];

        foreach ($default_attributes as $attribute) {
            /** @var  \Magento\Store\Model\Store $store */
            $store = $this->storeFactory->create();
            $store->load($attribute['store_code']);

            if (!$store->getId()) {
                /** @var \Magento\Store\Model\Website $website */
                $website = $this->websiteFactory->create();
                $website->load($attribute['website_code']);
                $website = $this->setWebID($website, $attribute);

                /** @var \Magento\Store\Model\Group $group */
                $group = $this->groupFactory->create();
                $group->load($attribute['group_code'], 'code');
                $group = $this->setGroupID($group, $website, $attribute);

                $store->setCode($attribute['store_code']);
                $store->setName($attribute['store_name']);
                $store->setWebsite($website);
                $store->setGroupId($group->getId());
                $store->setData('is_active', $attribute['is_active']);

                $this->storeResourceModel->save($store);

                $this->eventManager->dispatch('store_add', ['store' => $store]);
                $store = $this->storeFactory->create();
            }

        }

        $setup->endSetup();
    }

    /**
     * @param \Magento\Store\Model\Website $website
     * @param array $attribute
     * @return \Magento\Store\Model\Website
     */
    public function setWebID($website, $attribute)
    {
        if (!$website->getId()) {
            $website->setCode($attribute['website_code']);
            $website->setName($attribute['website_name']);
            $this->websiteResourceModel->save($website);
        }

        return $website;
    }

     /**
      * @param \Magento\Store\Model\Group $group
      * @param \Magento\Store\Model\Website $website
      * @param array $attribute
      * @return \Magento\Store\Model\Group
      */
    public function setGroupID($group, $website, $attribute)
    {
        if (!$group->getId()) {
            $group->setWebsiteId($website->getWebsiteId());
            $group->setCode($attribute['group_code']);
            $group->setName($attribute['group_name']);
            $group->setRootCategoryId($attribute['root_category_id']);
            $this->groupResourceModel->save($group);
        }

        return $group;
    }


    public function createOrUpdateRootCategory($categoryData, $categoryId = 0)
    {


        $category = $this->categoryFactory->create();

        if ($categoryId != 0) {
            $category->load($categoryId);
        }

        $category->setName($categoryData['name']);
        $category->setIsActive($categoryData['is_active']);
        $category->setStoreId(0);

        if ($categoryId == 0) {
            $parentCategory = $this->categoryFactory->create();
            $parentCategory->load(\Magento\Catalog\Model\Category::TREE_ROOT_ID);

            $category->setDisplayMode(\Magento\Catalog\Model\Category::DM_PRODUCT);
            $category->setPath($parentCategory->getPath());
        }

        $category->save();

        return $category->getId();
    }
}
