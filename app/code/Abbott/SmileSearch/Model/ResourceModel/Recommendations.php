<?php
namespace Abbott\SmileSearch\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Search\Model\QueryFactory;

class Recommendations extends \Magento\AdvancedSearch\Model\ResourceModel\Recommendations
{
    /**
     * Search query model
     *
     * @var \Magento\Search\Model\Query
     */
    protected $searchQueryModel;

    /**
     * Construct
     *
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $queryFactory, $connectionName);
        $this->searchQueryModel = $queryFactory->create();
    }

    /**
     * GetRecommendationsByQuery
     *
     * @param $query
     * @param $params
     * @param $searchRecommendationsCount
     * @return array
     * @throws LocalizedException
     */
    public function getRecommendationsByQuery($query, $params, $searchRecommendationsCount)
    {
        $this->searchQueryModel->loadByQueryText($query);

        if (isset($params['store_id'])) {
            $this->searchQueryModel->setStoreId($params['store_id']);
        }
        $relatedQueriesIds = $this->loadByQuery($query, $searchRecommendationsCount);
        $relatedQueries = [];
        if (count($relatedQueriesIds)) {
            $connection = $this->getConnection();
            $mainTable = $this->searchQueryModel->getResourceCollection()->getMainTable();
            $select = $connection->select()->from(
                ['main_table' => $mainTable],
                ['query_text', 'num_results']
            )->where(
                'query_id IN(?)',
                $relatedQueriesIds
            )->where(
                'num_results > 0'
            )->where('display_in_terms != 0');
            $relatedQueries = $connection->fetchAll($select);
        }
        return $relatedQueries;
    }
}
