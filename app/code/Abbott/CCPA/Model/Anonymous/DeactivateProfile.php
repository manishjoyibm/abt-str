<?php

namespace Abbott\CCPA\Model\Anonymous;

use Abbott\AwsLambda\Helper\Data;
use Abbott\AwsLambda\Logger\Log;
use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Customer\Model\CustomerFactory;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context as ModelContext;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeactivateProfile extends AbstractModel
{
    private const AWS_LAMBDA_LOG = 'Aws-Lambda-deactiveProfileApi : ';

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepoInterface;
    /**
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;
    /**
     * @var Data
     */
    protected Data $awsHelper;
    /**
     * @var Log
     */
    protected Log $log;
    /**
     * @var Random
     */
    protected Random $mathRandom;
    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;
    /**
     * @var ProfileManagementInterface
     */
    private ProfileManagementInterface $profileManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ProfileRepositoryInterface
     */
    private ProfileRepositoryInterface $profileRepository;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @param ModelContext $context
     * @param Registry $registry
     * @param CustomerRepositoryInterface $customerRepoInterface
     * @param Data $helper
     * @param Log $log
     * @param Random $mathRandom
     * @param CustomerFactory $customerFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param ManagerInterface $messageManager
     * @param ProfileManagementInterface $profileManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProfileRepositoryInterface $profileRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ModelContext                $context,
        Registry                    $registry,
        CustomerRepositoryInterface $customerRepoInterface,
        Data                        $helper,
        Log                         $log,
        Random                      $mathRandom,
        CustomerFactory             $customerFactory,
        AddressRepositoryInterface  $addressRepository,
        ManagerInterface            $messageManager,
        ProfileManagementInterface  $profileManagement,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        ProfileRepositoryInterface  $profileRepository,
        AbstractResource            $resource = null,
        AbstractDb                  $resourceCollection = null,
        array                       $data = []
    ) {
        $this->customerRepoInterface = $customerRepoInterface;
        $this->awsHelper = $helper;
        $this->log = $log;
        $this->mathRandom = $mathRandom;
        $this->customerFactory = $customerFactory;
        $this->addressRepository = $addressRepository;
        $this->messageManager = $messageManager;
        $this->profileManagement = $profileManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->profileRepository = $profileRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Deactivate Profile of given ID
     *
     * @param int $id
     * @return void
     */
    public function deactivateProfile($id): void
    {
        if ($id) {
            $customerObj = $this->customerFactory->create();
            $customer = $customerObj->load($id);

            $customerEmail = $customer->getEmail();
            $this->awsHelper->setStoreId($customer->getStoreId());

            if (!empty($customerEmail)) {
                if ($this->awsHelper->enabled()) {
                    $params = '{
                        "email": "' . $customerEmail . '"
                    }';

                    $this->deactivateCustomerProfile($params, $customer, $customerEmail);
                } else {
                    $this->makeAnonymous($customer);
                }
            } else {
                $this->messageManager->addError("Something went wrong.");
            }
        } else {
            $this->messageManager->addError("Something went wrong.");
        }
    }

    /**
     * Make Anonymous function
     *
     * @param Customer $customer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function makeAnonymous($customer): void
    {
        try {
            $randString = $this->mathRandom->getRandomString(8, '1234567890');
            $customerAddresses = [];

            foreach ($customer->getAddresses() as $address) {
                $customerAddresses[] = $address->toArray();
            }

            $customer->setFirstname('Anonymous');
            $customer->setLastname('Anonymous');
            if ($customer->getMiddlename()) {
                $customer->setMiddlename('Anonymous');
            }

            $customer->setEmail('Anonymous_' . $randString . '@abbott.com');
            $customer->setIsAnonymous(true);
            if ($customer->getWdUpi()) {
                $customer->setWdUpi('Anonymous');
            }
            if ($customer->getGigyaUid()) {
                $customer->setGigyaUid('Anonymous');
            }
            if ($customer->getAlternateEmail()) {
                $customer->setAlternateEmail('Anonymous_' . $randString . '@abbott.com');
            }

            $profiles = $this->getProfilesByCustomerId($customer->getId());

            foreach ($profiles as $profile) {
                $profileId = (int)$profile->getProfileId();
                $profileIncrementId = $profile->getIncrementId();
                $allowedStatuses = $this->profileManagement->getAllowedStatuses($profileId);
                if (in_array(Status::CANCELLED, $allowedStatuses)) {
                    try {
                        $this->profileManagement->changeStatusAction($profileId, Status::CANCELLED);
                        $this->log->writeLog(
                            self::AWS_LAMBDA_LOG . $profileIncrementId . ' The subscription successfully cancelled.'
                        );
                        $this->messageManager->addSuccessMessage(
                            $profileIncrementId . ' The subscription successfully cancelled.'
                        );
                    } catch (Exception $e) {
                        $this->log->writeLog(self::AWS_LAMBDA_LOG . $customer->getEmail() . " " . $e->getMessage());
                        $this->messageManager->addError("Error " . $e->getMessage());
                    }
                }
            }

            $customer->save();

            foreach ($customerAddresses as $customerAddress) {
                $this->changeAddress($customerAddress['entity_id']);
            }

            $this->messageManager->addSuccess('Customer account successfully deactivated.');
            $this->log->writeLog(self::AWS_LAMBDA_LOG . $customer->getEmail() . ' Account successfully deactivated.');
            return;
        } catch (Exception $e) {
            $this->log->writeLog(self::AWS_LAMBDA_LOG . $customer->getEmail() . " " . $e->getMessage());
            $this->messageManager->addError("Error " . $e->getMessage());
        }
    }

    /**
     * Get active,suspended,pause profiles
     *
     * @param int $customerId
     * @return ProfileInterface[]
     * @throws LocalizedException
     */
    public function getProfilesByCustomerId($customerId): array
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProfileInterface::CUSTOMER_ID, $customerId, 'eq')
            ->addFilter(ProfileInterface::STATUS, [Status::ACTIVE, Status::SUSPENDED, Status::PAUSE], 'in');

        $searchResults = $this->profileRepository->getList($this->searchCriteriaBuilder->create());

        return $searchResults->getItems();
    }

    /**
     * Change Address
     *
     * @param int $addressId
     * @return void
     * @throws LocalizedException
     */
    public function changeAddress($addressId): void
    {
        $address = $this->addressRepository->getById($addressId);
        $address->setFirstname('Anonymous');
        $address->setLastname('Anonymous');
        if ($address->getMiddlename()) {
            $address->setMiddlename('Anonymous');
        }
        $address->setStreet(['Anonymous']);
        $address->setCity('Anonymous');
        $address->setTelephone('9999999999');
        $this->addressRepository->save($address);
    }

    /**
     * Deactivate Customer Profile
     *
     * @param string $params
     * @param Customer $customer
     * @param String $customerEmail
     * @return void
     */
    private function deactivateCustomerProfile($params, $customer, $customerEmail): void
    {
        $this->log->writeLog(self::AWS_LAMBDA_LOG . print_r(["Request" => $params], true));
        $response = json_decode($this->awsHelper->deactivateProfile($params), true);
        $this->log->writeLog(self::AWS_LAMBDA_LOG . print_r(["Response" => $response], true));

        if (!empty($response['status']) && $response['status'] === true) {
            if ($response['errorCode'] == 0) {
                $this->makeAnonymous($customer);
            } else {
                $this->messageManager->addError("User account not found in Gigya.");
                $this->log->writeLog(self::AWS_LAMBDA_LOG . $customerEmail . ' User not found in Gigya');
                $this->makeAnonymous($customer);
            }
        } else {
            $this->messageManager->addError("API Authorization failed. Please check log for more details.");
            $this->log->writeLog(self::AWS_LAMBDA_LOG . $customerEmail . ' API Authorization failed.');
        }
    }
}
