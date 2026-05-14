<?php
namespace Abbott\PasswordHistory\Model;


use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Abbott\PasswordHistory\Api\UsedPasswordRepositoryInterface;
use Abbott\PasswordHistory\Model\ResourceModel\UsedPassword\Collection;
use Abbott\PasswordHistory\Model\ResourceModel\UsedPassword\CollectionFactory;
use Abbott\PasswordHistory\Model\UsedPasswordFactory;

class UsedPasswordRepository implements UsedPasswordRepositoryInterface
{
    /**
     * @var ResourceModel\UsedPassword
     */
    private $usedPasswordResource;

    /**
     * @var Collection
     */
    private $collectionFactory;

    /**
     * @var UsedPasswordFactory
     */
    private $usedPasswordFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessor
     */
    private $collectionProcessor;

    public function __construct(
        UsedPasswordFactory $usedPasswordFactory,
        ResourceModel\UsedPassword $usedPasswordResource,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessor $collectionProcessor
    ) {
        $this->usedPasswordResource = $usedPasswordResource;
        $this->collectionFactory = $collectionFactory;
        $this->usedPasswordFactory = $usedPasswordFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param UsedPassword $usedPassword
     * @return void
     * @throws CouldNotSaveException
     */
    public function save($usedPassword)
    {
        try {
            $this->usedPasswordResource->save($usedPassword);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save used password: %1', $exception->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $usedPassword = $this->usedPasswordFactory->create();

        $this->usedPasswordResource->load($usedPassword, $id);

        return $usedPassword;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * @param UsedPassword $usedPassword
     * @return void
     * @throws \Exception
     */
    public function delete($usedPassword)
    {
        try {
            $this->usedPasswordResource->delete($usedPassword);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete entry: %1', $exception->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getNew()
    {
        return $this->usedPasswordFactory->create();
    }
}
