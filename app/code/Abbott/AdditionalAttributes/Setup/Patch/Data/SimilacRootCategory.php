<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\GroupFactory;

class SimilacRootCategory implements DataPatchInterface
{
    const ROOT_NODE_ID = 1;

    const GROUP_CODE = 'new_similac_group';

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CategoryFactory */
    private $categoryFactory;

    /** @var CategoryRepositoryInterface */
    private $categoryRepo;

    /** @var LoggerInterface */
    private $log;

    /** @var GroupFactory */
    private $groupFactory;
    
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StoreManagerInterface $store
     * @param CategoryFactory $factory
     * @param CategoryRepositoryInterface $cat
     * @param LoggerInterface $logger
     * @param GroupFactory $group
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StoreManagerInterface $store,
        CategoryFactory $factory,
        CategoryRepositoryInterface $cat,
        LoggerInterface $logger,
        GroupFactory $group
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeManager = $store;
        $this->categoryFactory = $factory;
        $this->categoryRepo = $cat;
        $this->log = $logger;
        $this->groupFactory = $group;
    }

    public function apply()
    {
        $categoryName = "Category New Similac Website Store";
        $categoryUrl = "category-new-similac-website-store";
        $rootCategory = $this->categoryRepo
            ->get(self::ROOT_NODE_ID, $this->storeManager->getStore()->getId());
        if ($rootCategory) {
            try {
                $category = $this->categoryFactory->create();
                $category->setName($categoryName);
                $category->setIsActive(true);
                $category->setUrlKey($categoryUrl);
                $category->setData('description', 'description');
                $category->setParentId($rootCategory->getId());
                $category->setStoreId($this->storeManager->getStore()->getId());
                $category->setPath($rootCategory->getPath());
                $category->save();
                if ($category->getId()) {
                    $group = $this->groupFactory->create();
                    $group->load(self::GROUP_CODE, 'code');
                    if ($group->getGroupId()) {
                        $group->setRootCategoryId($category->getId())->save();
                    }
                }
            } catch (\Exception $e) {
                $this->log->critical('Similac Root Category Creation: ', ['exception' => $e]);
            }
        }
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
