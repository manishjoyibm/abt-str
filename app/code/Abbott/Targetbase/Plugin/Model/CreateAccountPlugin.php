<?php

namespace Abbott\Targetbase\Plugin\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

class CreateAccountPlugin
{

    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * createAccountPlugin constructor.
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        EventManager $eventManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->eventManager = $eventManager;
    }

    public function afterCreateAccount(
        \Magento\Customer\Api\AccountManagementInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {

        $this->eventManager->dispatch(
            'customer_register_success_custom',
            ['account_controller' => $this, 'customer' => $customer]
        );

        return $customer;
    }
}
