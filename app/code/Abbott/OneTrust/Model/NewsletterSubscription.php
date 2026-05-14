<?php

declare(strict_types=1);

namespace Abbott\OneTrust\Model;

use Abbott\OneTrust\Api\NewsletterSubscriptionInterface;
use Abbott\OneTrust\Logger\Logger;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Updates Customer Newsletter Subscription from OneTrust Event
 */
class NewsletterSubscription implements NewsletterSubscriptionInterface
{
    public const OT_STATUS_ACTIVE = 'ACTIVE';
    public const OT_STATUS_WITHDRAWN = 'WITHDRAWN';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var SubscriptionManagerInterface
     */
    protected $subscriptionManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Request $request
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepository $customerRepository
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param Logger $logger
     */
    public function __construct(
        Request $request,
        StoreManagerInterface $storeManager,
        CustomerRepository $customerRepository,
        SubscriptionManagerInterface $subscriptionManager,
        Logger $logger
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->subscriptionManager = $subscriptionManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function updateNewsletter(): string
    {
        $params = $this->request->getBodyParams();
        $email = $params['email'] ?? null;
        $status = $params['status'] ?? null;
        $result = 'Something went wrong with newsletter subscription.';
        if ($email == null || $email == '') {
            return $result;
        } else {
            try {
                $customer = $this->customerRepository->get($email);
                $storeId = (int)$customer->getStoreId();
                $customerId = (int)$customer->getId();
                if (empty($storeId)) {
                    $storeId = $this->storeManager->getStore()->getId();
                }

                if ($status == self::OT_STATUS_ACTIVE) {
                    $this->subscriptionManager->subscribeCustomer($customerId, $storeId);
                    $result = 'Successfully subscribed! Customer Email : ' . $email;
                } elseif ($status == self::OT_STATUS_WITHDRAWN) {
                    $this->subscriptionManager->unsubscribeCustomer($customerId, $storeId);
                    $result = 'Successfully unsubscribed! Customer Email : ' . $email;
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
                return $result . ' ' . $e->getMessage();
            }
        }
        $this->logger->info($result);
        return $result;
    }
}
