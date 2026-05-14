<?php
namespace Abbott\PasswordHistory\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\PasswordHistory\Api\Data\UsedPasswordInterface;
use Abbott\PasswordHistory\Api\UsedPasswordManagementInterface;
use Abbott\PasswordHistory\Api\UsedPasswordRepositoryInterface;
use Abbott\PasswordHistory\Helper\Config;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\InputException;


class UsedPasswordManagement implements UsedPasswordManagementInterface
{
    /**
     * @var UsedPasswordRepositoryInterface
     */
    private $passwordRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SortOrderBuilderFactory
     */
    private $sortOrderBuilderFactory;

     /**
     * @var CustomerRegistry
     */
    private $customerRegistry; 


    public function __construct(
        UsedPasswordRepositoryInterface $passwordRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        SortOrderBuilderFactory $sortOrderBuilderFactory,
        CustomerRepositoryInterface $customerRepository,
        EncryptorInterface $encryptor,
        Config $config,
        CustomerRegistry $customerRegistry

    ) {
        $this->passwordRepository = $passwordRepository;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->customerRepository = $customerRepository;
        $this->encryptor = $encryptor;
        $this->config = $config;
        $this->sortOrderBuilderFactory = $sortOrderBuilderFactory;       
        $this->customerRegistry         = $customerRegistry; 
    }

    /**
     * Check if the password is in the saved list of used passwords for the customer
     * Returns true if password is not on the list and throws and exception if it is
     *
     * @param string $email
     * @param string $password
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    
    public function validatePassword($email, $password)
    {
        if ($this->config->isEnabled()) {
            $customer = $this->customerRepository->get($email);

            // Seed once (first time) from customer_entity
            $this->seedCurrentHashIfHistoryEmpty($customer->getId());

            // Block "same as current" immediately
            $secureData  = $this->customerRegistry->retrieveSecureData($customer->getId());
            $currentHash = $secureData->getPasswordHash();
            if ($currentHash && $this->encryptor->validateHash($password, $currentHash)) {
                throw new InputException(__($this->config->getMessage()));
            }

            // Then check against last N used passwords
            /** @var SearchCriteriaBuilder $criteriaBuilder */
            $criteriaBuilder = $this->criteriaBuilderFactory->create();
            $criteriaBuilder->addFilter(UsedPasswordInterface::CUSTOMER_ID, $customer->getId());

            /** @var UsedPasswordInterface[] $usedPasswords */
            $usedPasswords = $this->passwordRepository->getList($criteriaBuilder->create())->getItems();

            foreach ($usedPasswords as $usedPassword) {
                if ($this->encryptor->validateHash($password, $usedPassword->getHash())) {
                    throw new InputException(__($this->config->getMessage()));
                }
            }
        }

        return true;
    }


    /**
     * Save password (as hash) to the list of used passwords for customer
     *
     * @param string $email
     * @param string $password
     * @return void
     * @throws LocalizedException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function saveUsedPassword($email, $password)
    {
        $customer = $this->customerRepository->get($email);

        $usedPassword = $this->passwordRepository->getNew();
        $usedPassword->setCustomerId($customer->getId());
        $usedPassword->setHash($this->encryptor->getHash($password, true));

        $this->passwordRepository->save($usedPassword);

        $this->cleanUpOldPasswords($customer->getId());
    }
    
    /**
     * Seed the customer's current password hash into history if it's empty.
     * This is called before validation and before saving to guarantee
     * the previous password can't be reused on the next attempt.
     *
     * @param int $customerId
     * @return void
     */
    private function seedCurrentHashIfHistoryEmpty($customerId)
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter(UsedPasswordInterface::CUSTOMER_ID, $customerId);

        $existing = $this->passwordRepository->getList($criteriaBuilder->create())->getItems();
        if (empty($existing)) {
            $secureData  = $this->customerRegistry->retrieveSecureData($customerId);
            $currentHash = $secureData->getPasswordHash();
            if ($currentHash) {
                $seed = $this->passwordRepository->getNew();
                $seed->setCustomerId($customerId);
                $seed->setHash($currentHash);
                $this->passwordRepository->save($seed);
                }
        }
    }
    

    /**
     * Remove all used passwords for customer except for configured number of last ones
     *
     * @param int $customerId
     * @return void
     * @throws CouldNotDeleteException
     */
    private function cleanUpOldPasswords($customerId)
    {
        /** @var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = $this->sortOrderBuilderFactory->create();

        $sortOrder = $sortOrderBuilder
            ->setField(UsedPasswordInterface::CREATED_AT)
            ->setDescendingDirection()
            ->create();

        /** @var SearchCriteriaBuilder $criteriaBuilderToKeep */
        $criteriaBuilderToKeep = $this->criteriaBuilderFactory->create();
        $criteriaBuilderToKeep->addFilter(UsedPasswordInterface::CUSTOMER_ID, $customerId);

        $criteriaBuilderAll = clone $criteriaBuilderToKeep;

        /** @var UsedPasswordInterface[] $passwordsAll */
        $passwordsAll = $this->passwordRepository->getList($criteriaBuilderAll->create())->getItems();

        $criteriaBuilderToKeep->addSortOrder($sortOrder);
        $criteriaBuilderToKeep->setPageSize($this->config->getHistorySize());
        $passwordsToKeep = $this->passwordRepository->getList($criteriaBuilderToKeep->create())->getItems();

        /** @var UsedPasswordInterface $password */
        foreach (array_diff(array_keys($passwordsAll), array_keys($passwordsToKeep)) as $passwordId) {
            $this->passwordRepository->delete($passwordsAll[$passwordId]);
        }
    }
}
