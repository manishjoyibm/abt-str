<?php


namespace Abbott\Catalog\Plugin\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductSearchPlugin
 * @package Abbott\Catalog\Plugin\CatalogGraphQl\Model\Resolver\Products\DataProvider
 */
class ProductSearchPlugin
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Abbott\Catalog\Helper\Data
     */
    private $helper;

    /**
     * ProductSearchPlugin constructor.
     * @param StoreManagerInterface $storeManager
     * @param \Abbott\Catalog\Helper\Data $helper
     */
    public function __construct(StoreManagerInterface $storeManager, \Abbott\Catalog\Helper\Data $helper)
    {

        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }


    /**
     * @param ProductSearch $subject
     * @param SearchCriteriaInterface $searchCriteria
     * @param SearchResultInterface $searchResult
     * @param array $attributes
     */
    public function beforeGetList(
        ProductSearch $subject,
        SearchCriteriaInterface $searchCriteria,
        SearchResultInterface $searchResult,
        array $attributes = []
    ): array
    {
        if ($this->helper->isDisableSaleEnabled($this->storeManager->getStore()->getId())) {
            $attributes[] = "disable_sale";
        }
        $attributes[] = "disable_sale";
        return [$searchCriteria, $searchResult, $attributes];
    }
}
