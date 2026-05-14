<?php
namespace Abbott\Sarp2\Controller\Subscription;

use Abbott\Sarp2\Helper\ChangeSubscription;
use Abbott\Sarp2\Helper\Data;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileItemRepositoryInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Abbott\Subscriptionhistory\Helper\Data as HistoryDataLog;

class Update extends \Magento\Framework\App\Action\Action
{
    const CHANGE_SUBSCIPTION_PLAN_QUANTITY_EVENT = 'subscription_profile_qty_change';

    protected $resultJsonFactory;
    protected $profileRepository;
    protected $profileItemRepository;
    protected $helper;
    protected $productRepository;
    protected $updateSubscribe;
    protected $customerSession;
    protected $searchCriteriaBuilder;
    protected $formKeyValidator;
    protected $logger;
    protected $checkoutHelper;
    protected $historyDataLog;
    protected $historyMessage;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ProfileRepositoryInterface $profileRepository,
        Data $helper,
        ChangeSubscription $updateSubscribe,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Psr\Log\LoggerInterface $logger,
        ProfileItemRepositoryInterface $profileItemRepository,
        ProductRepositoryInterface $productRepository,
        \Abbott\Checkout\Helper\Data $checkoutHelper,
        HistoryDataLog $historyDataLog,
        \Abbott\Subscriptionhistory\Helper\HistoryMessages $historyMessage
    ) {
        parent::__construct($context);
        $this->resultJsonFactory      = $resultJsonFactory;
        $this->profileRepository     = $profileRepository;
        $this->profileItemRepository = $profileItemRepository;
        $this->helper                = $helper;
        $this->updateSubscribe       = $updateSubscribe;
        $this->customerSession       = $customerSession;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formKeyValidator      = $formKeyValidator;
        $this->logger                = $logger;
        $this->productRepository     = $productRepository;
        $this->checkoutHelper        = $checkoutHelper;
        $this->historyDataLog        = $historyDataLog;
        $this->historyMessage        = $historyMessage;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            if (!$this->customerSession->create()->isLoggedIn()) {
                throw new LocalizedException(__('Invalid customer. Please login again.'));
            }

            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid form key. Please refresh the page.'));
            }

            $post = $this->getRequest()->getParams();

            $profileId     = $post['profile_id'] ?? null;
            $profileItemId = $post['profileItemId'] ?? null;
            $qty           = (isset($post['qty']) && (int)$post['qty'] > 0) ? (int)$post['qty'] : null;

            if (!$profileId || !$profileItemId || !$qty) {
                throw new LocalizedException(__('Invalid request parameters.'));
            }

            $customerId = $this->customerSession->create()->getCustomer()->getId();

            if (!$this->checkCustomerProfile($customerId, $profileId)) {
                throw new LocalizedException(__('Invalid subscription profile.'));
            }

            $profileItem = $this->profileItemRepository->get($profileItemId);
            $oldQty      = (int)$profileItem->getQty();
            $sku         = $profileItem->getSku();
            $productId   = $profileItem->getProductId();

            if ($this->checkoutHelper->isEnabledQuantityValidation()) {
                $product = $this->productRepository->getById($productId);

                $minQty = (int)($product->getData('cans_y_min_update') ?: 1);
                $maxQty = (int)($product->getData('cans_x_max_update') ?: 0);

                if ($qty < $minQty || ($maxQty && $qty > $maxQty)) {
                    return $resultJson->setData([
                        'success' => false,
                        'message' => __("Min {$minQty} and Max {$maxQty} quantity required.")
                    ]);
                }
            }

            /** Update item quantity */
            $profileItem->setQty($qty);
            $this->profileItemRepository->save($profileItem);

            /** Update profile total qty */
            $profile = $this->profileRepository->get($profileId);
            $totalQty = $this->getProfileItemUpdate($profile->getItems());
            $profile->setItemsQty($totalQty);
            $this->profileRepository->save($profile);

            /** History log */
            if ($qty !== $oldQty && $this->historyDataLog->getSubscriptionHistoryStatus($profile->getStoreId())) {
                $oldData = [self::CHANGE_SUBSCIPTION_PLAN_QUANTITY_EVENT => [$sku => $oldQty]];
                $newData = [self::CHANGE_SUBSCIPTION_PLAN_QUANTITY_EVENT => [$sku => $qty]];

                $this->historyDataLog->prepareFrontendData(
                    $profile,
                    self::CHANGE_SUBSCIPTION_PLAN_QUANTITY_EVENT,
                    $oldData,
                    $newData
                );
            }

            /** Email */
            if ($this->helper->getUpdateMailEnabled()) {
                $this->updateSubscribe->updateSubscriptionNotification();
            }

            return $resultJson->setData([
                'success' => true,
                'message' => __('Subscription product quantity updated successfully.')
            ]);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getProfileItemUpdate($profileItems)
    {
        $total = 0;
        foreach ($profileItems as $item) {
            $total += (int)$item->getQty();
        }
        return $total;
    }

    public function checkCustomerProfile($customerId, $profileId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProfileInterface::CUSTOMER_ID, $customerId)
            ->addFilter(ProfileInterface::PROFILE_ID, $profileId)
            ->create();

        return (bool)count(
            $this->profileRepository->getList($searchCriteria)->getItems()
        );
    }
}