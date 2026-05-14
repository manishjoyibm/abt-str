<?php


namespace Abbott\Sarp2\Model;


use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Validation\ValidationException;

/**
 * Class PaymentChangeLogManager
 * @package Abbott\Sarp2\Model
 */
class PaymentChangeLogManager
{

    const TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var PaymentChangeLogFactory
     */
    protected $paymentChangeLogFactory;
    /**
     * @var ResourceModel\PaymentChangeLog
     */
    protected $paymentChangeLogResource;
    /**
     * @var ResourceModel\PaymentChangeLog\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var \Abbott\Sarp2\Helper\Data
     */
    protected $helper;

    /**
     * PaymentChangeLogManager constructor.
     * @param PaymentChangeLogFactory $paymentChangeLogFactory
     * @param ResourceModel\PaymentChangeLog $paymentChangeLogResource
     * @param ResourceModel\PaymentChangeLog\CollectionFactory $collectionFactory
     * @param DateTime $date
     * @param \Abbott\Sarp2\Helper\Data $helper
     */
    public function __construct(
        PaymentChangeLogFactory $paymentChangeLogFactory,
        \Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog $paymentChangeLogResource,
        \Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog\CollectionFactory $collectionFactory,
        DateTime $date,
        \Abbott\Sarp2\Helper\Data $helper
    ) {

        $this->paymentChangeLogFactory = $paymentChangeLogFactory;
        $this->paymentChangeLogResource = $paymentChangeLogResource;
        $this->collectionFactory = $collectionFactory;
        $this->date = $date;
        $this->helper = $helper;
    }

    /**
     * @param int $customerId
     * @param int $profileId
     * @param string|int $tokenId
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function addRecord($customerId, $hasFailed = false, $profileId = null, $tokenId = null) {
        /** @var PaymentChangeLog $paymentChangeLog */
        $paymentChangeLog = $this->paymentChangeLogFactory->create();
        $paymentChangeLog->setCustomerId($customerId);
        $paymentChangeLog->setProfileId($profileId);
        $paymentChangeLog->setToken($tokenId);
        $paymentChangeLog->setHasFailed($hasFailed);
        $this->paymentChangeLogResource->save($paymentChangeLog);
    }


    /**
     * @param int $customerId
     * @return bool
     */
    public function validateFailedPaymentChanges($customerId): bool {
        $timeLimit = $this->helper->getPaymentChangeTimeLimitInvalid();
        $limit = $this->helper->getPaymentChangeLimitInvalid();
        $expiryTime = strtotime("-".$timeLimit." minutes", strtotime(date(self::TIME_FORMAT)));
        $endTime = $this->date->date(self::TIME_FORMAT, $expiryTime);
        /** @var \Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('has_failed', true);
        $collection->addFieldToFilter('created_at', array('gteq' => $endTime));
        if($collection->getSize() >= $limit) {
            throw new ValidationException(__($this->helper->getPaymentChangeLimitInvalidMessage()));
        }
        return true;
    }


    /**
     * @param int $customerId
     * @param int $profileId
     * @return bool
     */
    public function validatePaymentChanges($customerId, $profileId = null): bool {
        $timeLimit = $this->helper->getPaymentChangeTimeLimitPerProfile();
        $limit = $this->helper->getPaymentChangeLimitPerProfile();
        $expiryTime = strtotime("-".$timeLimit." minutes", strtotime(date(self::TIME_FORMAT)));
        $endTime = $this->date->date(self::TIME_FORMAT, $expiryTime);
        /** @var \Abbott\Sarp2\Model\ResourceModel\PaymentChangeLog\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('profile_id', $profileId);
        $collection->addFieldToFilter('created_at', array('gteq' => $endTime));
        if($collection->getSize() >= $limit) {
            throw new ValidationException(__($this->helper->getPaymentChangeLimitPerProfileMessage()));
        }
        return true;
    }
}
