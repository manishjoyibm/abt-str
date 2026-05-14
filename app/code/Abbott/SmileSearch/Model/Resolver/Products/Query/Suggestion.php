<?php

declare(strict_types=1);

namespace Abbott\SmileSearch\Model\Resolver\Products\Query;

use Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Search\Model\Query;
use Magento\Search\Model\ResourceModel\Query as QueryResource;
use Magento\Store\Model\StoreManagerInterface;

class Suggestion
{
    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_ENABLED
     */
    public const CONFIG_IS_ENABLED = 'catalog/search/search_recommendations_enabled';

    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_COUNT_RESULTS_ENABLED
     */
    public const CONFIG_RESULTS_COUNT_ENABLED =
        'catalog/search/search_recommendations_count_results_enabled';

    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_COUNT
     */
    public const CONFIG_RESULTS_COUNT = 'catalog/search/search_recommendations_count';

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $searchLayer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RecommendationsFactory
     */
    private $recommendationsFactory;
    /**
     * @var ObjectManagerInterface
     */

    protected $queryClass;

    private $queryresourcemodel;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Construct
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Resolver $layerResolver
     * @param RecommendationsFactory $recommendationsFactory
     * @param StoreManagerInterface $storeManager
     * @param Query $queryClass
     * @param QueryResource $queryresourcemodel
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Resolver $layerResolver,
        RecommendationsFactory $recommendationsFactory,
        StoreManagerInterface $storeManager,
        Query $queryClass,
        QueryResource $queryresourcemodel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->searchLayer = $layerResolver->get();
        $this->recommendationsFactory = $recommendationsFactory;
        $this->storeManager = $storeManager;
        $this->queryClass = $queryClass;
        $this->queryresourcemodel = $queryresourcemodel;
    }

    /**
     * GetSuggestion function
     *
     * @param $query
     * @param $totalCount
     * @return array
     * @throws LocalizedException
     */
    public function getSuggestion($query, $totalCount)
    {
        $queryobject =$this->queryClass->loadByQuery($query);
        $recommendations = [];
        if (!$this->isSearchRecommendationsEnabled()) {
            return [];
        }
        foreach ($this->getSearchRecommendations($queryobject) as $recommendation) {
            $recommendations['search_suggestion'][] =
                [
                    'queryText' => $recommendation['query_text'],
                    'resultsCount' => $recommendation['num_results'],
                ];
        }
        return $recommendations;
    }

    /**
     * Is Results Count Enabled
     *
     * @return bool
     */
    public function isResultsCountEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_RESULTS_COUNT_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return Search Recommendations
     *
     * @param QueryInterface $query
     * @return array
     */
    private function getSearchRecommendations(\Magento\Search\Model\QueryInterface $query)
    {
        $recommendations = [];
        if ($this->isSearchRecommendationsEnabled()) {
            $productCollection = $this->searchLayer->getProductCollection();
            $params = ['store_id' => $productCollection->getStoreId()];
            if (empty($query->getQueryText()) || $query->getQueryText() == '0') {
                return [];
            }
            $queryText = (!ctype_space($query->getQueryText())) ? $query->getQueryText() : '';
            /** @var \Magento\AdvancedSearch\Model\ResourceModel\Recommendations $recommendationsResource */
            $recommendationsResource = $this->recommendationsFactory->create();
            $recommendations = $recommendationsResource->getRecommendationsByQuery(
                $queryText,
                $params,
                $this->getSearchRecommendationsCount()
            );
        }
        return $recommendations;
    }

    /**
     * Is Search Recommendations Enabled
     *
     * @return bool
     */
    private function isSearchRecommendationsEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return Search Recommendations Count
     *
     * @return int
     */
    private function getSearchRecommendationsCount()
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_RESULTS_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
