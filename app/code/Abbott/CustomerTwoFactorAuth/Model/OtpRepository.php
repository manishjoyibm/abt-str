<?php
namespace Abbott\CustomerTwoFactorAuth\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface;
use Abbott\CustomerTwoFactorAuth\Api\Data\OtpSearchResultsInterfaceFactory;
use Abbott\CustomerTwoFactorAuth\Api\OtpRepositoryInterface;
use Abbott\CustomerTwoFactorAuth\Model\ResourceModel\Otp;
use Abbott\CustomerTwoFactorAuth\Model\ResourceModel\Otp\CollectionFactory;

class OtpRepository implements OtpRepositoryInterface
{
    /**
     * @var OtpFactory
     */
    private $otpFactory;

    /**
     * @var Otp
     */
    private $otpResource;

    /**
     * @var OtpCollectionFactory
     */
    private $otpCollectionFactory;

    /**
     * @var OtpSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param OtpFactory $otpFactory
     * @param Otp $otpResource
     * @param CollectionFactory $otpCollectionFactory
     * @param OtpSearchResultsInterfaceFactory $otpSearchResultsInterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        OtpFactory $otpFactory,
        Otp $otpResource,
        CollectionFactory $otpCollectionFactory,
        OtpSearchResultsInterfaceFactory $otpSearchResultsInterfaceFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->otpFactory = $otpFactory;
        $this->otpResource = $otpResource;
        $this->otpCollectionFactory = $otpCollectionFactory;
        $this->searchResultsFactory = $otpSearchResultsInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param int $id
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $otp = $this->otpFactory->create();
        $this->otpResource->load($otp, $id);
        if (!$otp->getId()) {
            throw new NoSuchEntityException(__('Unable to find Otp with ID "%1"', $id));
        }
        return $otp;
    }

    /**
     * @param \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface $otp
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(OtpInterface $otp)
    {
        $this->otpResource->save($otp);
        return $otp;
    }

    /**
     * @param \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface $otp
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(OtpInterface $otp)
    {
        try {
            $this->otpResource->delete($otp);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->otpCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }
}
