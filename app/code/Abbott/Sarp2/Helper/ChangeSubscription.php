<?php

namespace Abbott\Sarp2\Helper;

class ChangeSubscription {

    public $storeManager;
    public $profile;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    public $transportBuilder;
    public $helper;
    public $productRepository;
    public $logger;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param Profile $profile
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Sarp2\Model\Profile $profile,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Abbott\Sarp2\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Psr\Log\LoggerInterface $logger

    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->profile = $profile;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Perform update subscription
     *
     */
    public function updateSubscriptionNotification($profileId = '')
    {
        if(empty($profileId))
        {
            $profileId = $this->request->getParam('profile_id');
        }
        $profile = $this->profile->load($profileId);
        $incrementId = $profile->getIncrementId();
        $createdAt = date_create($profile->getCreatedAt());
        $createdAtFormatted = date_format($createdAt, "M j, Y, G:i:s A ");
        $customerName = $this->profile->getCustomerFullname();
        $receiverEmail = $this->profile->getCustomerEmail();
        $storeId = $this->profile->getStoreId();
        $store = $this->storeManager->getStore();
        $url = $this->helper->getStoreUrl();
        $storePhone = $this->helper->getStorePhone();
        $storeName = $this->storeManager->getStore()->getFrontendName();
        $templateVars = [
                  'store' => $store,
                  'profileIncrementId'  => $incrementId,
                  'created_at_formatted' => $createdAtFormatted,
                  'storeName' => $storeName,
                  'url' => $url,
                  'customerEmail' => $receiverEmail,
                  'customerName' => $customerName,
                  'storePhone' => $storePhone
                ];
               
        $template = $this->helper->getUpdateTemplate();
        $sender = $this->helper->getSenderEmail();
       
        $transport = $this->transportBuilder
        ->setTemplateIdentifier($template)
        ->setTemplateOptions(
            [
                 'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                 'store' => $this->storeManager->getStore()->getId(),
             ]
        )->setTemplateVars(
            $templateVars
        )->setFrom(
            $sender
        )->addTo(
            $receiverEmail
        )->getTransport();
        $transport->sendMessage();
    }

    /**
     * Perform cancel subscription
     *
     */
    public function cancelSubscriptionNotification($profileId = null)
    {
        $profileId = ($profileId) ? $profileId : $this->request->getParam('profile_id');
        $profile = $this->profile->load($profileId);
        $incrementId = $profile->getIncrementId();
        $createdAt = date_create($profile->getCreatedAt());
        $createdAtFormatted = date_format($createdAt, "M j, Y, G:i:s A ");
        $customerName = $this->profile->getCustomerFullname();
        $receiverEmail = $this->profile->getCustomerEmail();
        $storeId = $this->profile->getStoreId();
        $store = $this->storeManager->getStore($storeId);
        $url = $this->helper->getStoreUrl();
        $this->helper->setStore($store);
        $storePhone = $this->helper->getStorePhone();
        $storeName = $store->getFrontendName();
        $templateVars = [
              'store' => $store,
              'profileIncrementId'    => $incrementId,
              'created_at_formatted' => $createdAtFormatted,
              'storeName' => $storeName,
              'url' => $url,
              'customerEmail' => $receiverEmail,
              'customerName' => $customerName,
              'storePhone' => $storePhone
            ];
        $template = $this->helper->getCancelTemplate();
        $sender = $this->helper->getSenderEmail();
        $transport = $this->transportBuilder
        ->setTemplateIdentifier($template)
        ->setTemplateOptions(
            [
             'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
             'store' => $storeId,
            ]
        )->setTemplateVars(
            $templateVars
        )->setFromByScope(
            $sender, $storeId
        )->addTo(
            $receiverEmail
        )->getTransport();
        $transport->sendMessage();
    }

    /**
     * Perform update subscription
     *
     */
    public function updateSubscriptionNotificationAdminhtml()
    {
        $profileId = $this->request->getParam('profile_id');
        $profile = $this->profile->load($profileId);
        $incrementId = $profile->getIncrementId();
        $createdAt = date_create($profile->getCreatedAt());
        $createdAtFormatted = date_format($createdAt, "M j, Y, G:i:s A ");
        $customerName = $profile->getCustomerFullname();
        $receiverEmail = $profile->getCustomerEmail();
        $storeId = $profile->getStoreId();
        $store = $this->storeManager->getStore($storeId);
        $url = $store->getUrl();
        $storePhone = $this->helper->getStorePhone();
        $storeName = $store->getFrontendName();
        $templateVars = [
                  'store' => $store,
                  'profileIncrementId'  => $incrementId,
                  'created_at_formatted' => $createdAtFormatted,
                  'storeName' => $storeName,
                  'url' => $url,
                  'customerEmail' => $receiverEmail,
                  'customerName' => $customerName,
                  'storePhone' => $storePhone
                ];
        $template = $this->helper->getStoreUpdateTemplate($storeId);

        $sender = [
            'name' => $this->helper->getStoreSenderName($storeId),
            'email' => $this->helper->getStoreSenderEmail($storeId)
        ];

        $transport = $this->transportBuilder
        ->setTemplateIdentifier($template)
        ->setTemplateOptions(
            [
                 'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                 'store' => $storeId,
             ]
        )->setTemplateVars(
            $templateVars
        )->setFrom(
            $sender
        )->addTo(
            $receiverEmail
        )->getTransport();
        $transport->sendMessage();
    }

     /**
     * Perform Out Of Stock Notification
     *
     */
    public function sendItemOutOfStockNotification($profileId = null, $items = [])
    {
        try {
            if (empty($profileId)) {
                $profileId = $this->request->getParam('profile_id');
            }
            $profile = $this->profile->load($profileId);
            $incrementId = $profile->getIncrementId();
            $createdAt = date_create($profile->getCreatedAt());
            $createdAtFormatted = date_format($createdAt, "M j, Y, G:i:s A ");
            $customerName = $this->profile->getCustomerFullname();
            $receiverEmail = $this->profile->getCustomerEmail();
            $storeId = $this->profile->getStoreId();
            $store = $this->storeManager->getStore();
            $url = $this->helper->getStoreUrl();
            $storePhone = $this->helper->getStorePhone();
            $storeName = $this->storeManager->getStore()->getFrontendName();
            $emailItemstpl = '';
            if (count($items) > 0) {
                $emailItemstpl .= '<ul>';
                foreach ($items as $item) {
                    $_product =  $this->productRepository->get($item);
                    $emailItemstpl .= '<li>' . $_product->getName() . '</li>';
                }
                $emailItemstpl .= '</ul>';
            }
            $templateVars = [
                'store' => $store,
                'profileIncrementId' => $incrementId,
                'created_at_formatted' => $createdAtFormatted,
                'storeName' => $storeName,
                'url' => $url,
                'customerEmail' => $receiverEmail,
                'customerName' => $customerName,
                'storePhone' => $storePhone,
                'outOfStockItems' => $emailItemstpl,

            ];

            $template = $this->helper->getOutOfStockUpdateTemplate();
            $sender = $this->helper->getSenderEmail();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )->setTemplateVars(
                $templateVars
            )->setFrom(
                $sender
            )->addTo(
                $receiverEmail
            )->getTransport();
            $transport->sendMessage();

        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
