<?php

declare(strict_types=1);

namespace Abbott\SmileSearch\Rewrite\Magento\CatalogGraphQl\Model\Resolver;

use Abbott\AwsLambda\Logger\Log;
use Magento\Catalog\Model\Session;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Filter;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\SearchFilter;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Abbott\SmileSearch\Model\Resolver\Products\Query\Suggestion;
use Abbott\MyAccount\Helper\Data;
use Abbott\SmileSearch\Helper\Data as SmileSearchHelper;

class Products extends \Magento\CatalogGraphQl\Model\Resolver\Products
{
    public $catalogSession;
    public $log;
    public $helper;
    public const SEARCH = 'search';
    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Search
     */
    private $searchQuery;

    /**
     * @var Filter
     */
    private $filterQuery;

    /**
     * @var SearchFilter
     */
    private $searchFilter;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchApiCriteriaBuilder;

    private $suggestion;

    /**
     * @var SmileSearchHelper
     */
    private $smilesearchHelper;

    /**
     * Products constructor.
     *
     * @param Builder $searchCriteriaBuilder
     * @param Search $searchQuery
     * @param Filter $filterQuery
     * @param SearchFilter $searchFilter
     * @param Suggestion $suggestion
     * @param SearchCriteriaBuilder|null $searchApiCriteriaBuilder
     * @param Session $catalogSession
     * @param Log $log
     * @param Data $helper
     * @param SmileSearchHelper $smilesearchHelper
     */
    public function __construct(
        Builder $searchCriteriaBuilder,
        Search $searchQuery,
        Filter $filterQuery,
        SearchFilter $searchFilter,
        Suggestion $suggestion,
        Session $catalogSession,
        Log $log,
        Data $helper,
        SmileSearchHelper $smilesearchHelper,
        SearchCriteriaBuilder $searchApiCriteriaBuilder = null
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchQuery = $searchQuery;
        $this->filterQuery = $filterQuery;
        $this->searchFilter = $searchFilter;
        $this->suggestion = $suggestion;
        $this->catalogSession = $catalogSession;
        $this->log = $log;
        $this->helper = $helper;
        $this->smilesearchHelper = $smilesearchHelper;
        $this->searchApiCriteriaBuilder = $searchApiCriteriaBuilder ??
            \Magento\Framework\App\ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
    }

    /**
     * Resolver
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if (!isset($args[self::SEARCH]) && !isset($args['filter'])) {
            throw new GraphQlInputException(
                __("'search' or 'filter' input argument is required.")
            );
        }
        if ($this->helper->getProductAttributeConfig() && isset($args['sort']['relevance'])
        ) {
            $args['sort']['product_family_order'] = 'ASC';
            $args['sort']['order_in_family'] = 'ASC';
            unset($args['sort']['relevance']);
        }
        $this->log->writeLog('checkt he arguments for product query arguments');
        $this->log->writeLog(print_r($args, true));
        /** set profile id value in session **/
        if (!empty($args['profile_id'])) {
            $this->catalogSession->unsProfileIds();
            $this->catalogSession->setProfileIds($args['profile_id']);
        }
        $searchResult = $this->searchQuery->getResult($args, $info, $context);
        $searchResultMessage = '';
        if ($searchResult->getTotalCount() == 0 && isset($args[self::SEARCH])) {
            $spellCorrection = $this->smilesearchHelper->spellCorrection($args['search']);
            if ($spellCorrection) {
                $args['search'] = $spellCorrection;
                $searchResult = $this->searchQuery->getResult($args, $info, $context);
                if ($searchResult->getTotalCount() > 0) {
                    $searchResultMessage = "Your search for ".$args['search']." did not
                    match any products. Showing results for: ".$spellCorrection;
                }
            }
        }
        if ($searchResult->getCurrentPage() > $searchResult->getTotalPages() &&
            $searchResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchResult->getCurrentPage(), $searchResult->getTotalPages()]
                )
            );
        }
        if (isset($args[self::SEARCH])) {
            $suggestions = $this->suggestion->getSuggestion($args[self::SEARCH], $searchResult->getTotalCount());
        }
        if (isset($args[self::SEARCH]) && !empty($suggestions)) {
            $data = [
                'total_count' => $searchResult->getTotalCount(),
                'items' => $searchResult->getProductsSearchResult(),
                'page_info' => [
                    'page_size' => $searchResult->getPageSize(),
                    'current_page' => $searchResult->getCurrentPage(),
                    'total_pages' => $searchResult->getTotalPages()
                ],
                'search_result' => $searchResult,
                'layer_type' => isset($args[self::SEARCH]) ?
                    Resolver::CATALOG_LAYER_SEARCH : Resolver::CATALOG_LAYER_CATEGORY,
                'search_suggestion' => $suggestions['search_suggestion'],
                'search_result_message' => $searchResultMessage
            ];
        } else {
            $data = [
                'total_count' => $searchResult->getTotalCount(),
                'items' => $searchResult->getProductsSearchResult(),
                'page_info' => [
                    'page_size' => $searchResult->getPageSize(),
                    'current_page' => $searchResult->getCurrentPage(),
                    'total_pages' => $searchResult->getTotalPages()
                ],
                'search_result' => $searchResult,
                'layer_type' => isset($args[self::SEARCH]) ?
                    Resolver::CATALOG_LAYER_SEARCH : Resolver::CATALOG_LAYER_CATEGORY,
                'search_result_message' => $searchResultMessage
            ];
        }
        return $data;
    }
}
