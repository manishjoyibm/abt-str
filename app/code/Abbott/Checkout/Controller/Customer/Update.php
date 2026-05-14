<?php

namespace Abbott\Checkout\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Abbott\AwsLambda\Logger\Log;

class Update extends \Magento\Framework\App\Action\Action
{
    public $customerRepositoryInterface;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepo;

    /**
     * @var \Abbott\AwsLambda\Logger\Log
     */
    protected $log;

    /**
     * @var Session
     */
    protected $customerSession;



    /**
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $resultJsonFactory
     * @param CustomerRepositoryInterface $customerRepo
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepo
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepo;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $result->setData(['success' => false]);
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $customer =$this->customerRepositoryInterface->getById($customerId);
            $isSSM = (!empty($customer->getCustomAttribute('user_type'))) ?
                $customer->getCustomAttribute('user_type')->getValue() : '';
            $ssmOrderFlag = (!empty($customer->getCustomAttribute('ssm_order_flag'))) ?
                $customer->getCustomAttribute('ssm_order_flag')->getValue(): 0;
            if ($isSSM == \Abbott\Strongmoms\Helper\Data::IS_SIMILAC_SSM && $ssmOrderFlag != 1) {
                $customer->setCustomAttribute('ssm_order_flag', 1);
                $this->customerRepositoryInterface->save($customer);
                $result->setData(['success' => true]);
            }
        }
         return $result;
    }
}
