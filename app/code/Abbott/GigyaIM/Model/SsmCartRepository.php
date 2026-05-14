<?php

namespace Abbott\GigyaIM\Model;

use Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\GigyaIM\Api\Data\SsmCartInterface;
use Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterfaceFactory;
use Abbott\GigyaIM\Api\SsmCartRepositoryInterface;
use Abbott\GigyaIM\Model\ResourceModel\SsmCart\CollectionFactory as SsmCartCollectionFactory;
use Abbott\GigyaIM\Model\ResourceModel\SsmCart\Collection;
use Abbott\GigyaIM\Model\SsmCartFactory;
use Abbott\GigyaIM\Model\ResourceModel\SsmCart as SsmCartResource;

class SsmCartRepository implements SsmCartRepositoryInterface
{

    /**
     * @var \Abbott\GigyaIM\Model\SsmCartFactory
     */
    protected $ssmFactory;

    /**
     * @var SsmCartCollectionFactory
     */
    protected $ssmCartCollFactory;

    /**
     * @var SsmCartSearchResultsInterfaceFactory
     */
    protected $ssmSearchResultFactory;

    /**
     * @var SsmCartResource
     */
    protected $resource;

    /**
     * Construct function
     *
     * @param SsmCartFactory $factory
     * @param SsmCartCollectionFactory $ssmCartCollFactory
     * @param SsmCartSearchResultsInterfaceFactory $ssmSearchResultFactory
     * @param SsmCartResource $resource
     */
    public function __construct(
        SsmCartFactory $factory,
        SsmCartCollectionFactory $ssmCartCollFactory,
        SsmCartSearchResultsInterfaceFactory $ssmSearchResultFactory,
        SsmCartResource $resource
    ) {
        $this->ssmFactory = $factory;
        $this->ssmCartCollFactory = $ssmCartCollFactory;
        $this->ssmSearchResultFactory = $ssmSearchResultFactory;
        $this->resource = $resource;
    }

    /**
     * GetById function
     *
     * @param int $id
     * @return SsmCartInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $ssmCart = $this->ssmFactory->create();
        $ssmCart->getResource()->load($ssmCart, $id);
        if (!$ssmCart->getId()) {
            throw new NoSuchEntityException(__('Unable to find ssm shopping cart with ID "%1"', $id));
        }
        return $ssmCart;
    }

    /**
     * Save function
     *
     * @param SsmCartInterface $ssmCartInterface
     * @return SsmCartInterface|SsmCartResource
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(SsmCartInterface $ssmCartInterface)
    {
        return $this->resource->save($ssmCartInterface);
    }

    /**
     * Delete function
     *
     * @param SsmCartInterface $ssmCartInterface
     * @return void
     */
    public function delete(SsmCartInterface $ssmCartInterface)
    {
        $ssmCart = $this->ssmFactory->create();
        $ssmCart->getResource()->delete($ssmCartInterface);
    }

    /**
     * DeleteById function
     *
     * @param $id
     * @return void
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        $ssmCart = $this->ssmFactory->create();
        $ssmCart->getResource()->load($ssmCart, $id);
        if (!$ssmCart->getId()) {
            throw new NoSuchEntityException(__('Unable to find ssm shopping cart with ID "%1"', $id));
        }
        $ssmCart->delete();
    }

    /**
     * GetByEmail function
     *
     * @param string $email
     * @param mixed|null $websiteId
     * @return SsmCartInterface
     */
    public function getByEmail($email, $websiteId = null)
    {
        $ssmCart = $this->ssmFactory->create();
        return $ssmCart->getResource()->getByEmailAndWebsite($email, $websiteId);
    }

    /**
     * GetList function
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SsmCartSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->ssmCartCollFactory->create();

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * AddFiltersToCollection function
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return void
     */
    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType()
                    ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
    }

    /**
     * AddSortOrdersToCollection function
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return void
     */
    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
            $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
        }
    }

    /**
     * AddPagingToCollection function
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return void
     */
    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * Build Search Result
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return mixed
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->ssmSearchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
