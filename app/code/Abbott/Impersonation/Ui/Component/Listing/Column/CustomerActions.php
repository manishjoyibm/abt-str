<?php

namespace Abbott\Impersonation\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;

class CustomerActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var StoreRepository
     */
    protected $storeRepository;
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param ContextInterface $context
     * @param StoreRepository $storeRepository
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization,
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreRepository $storeRepository,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_authorization = $authorization;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $hidden = !$this->_authorization->isAllowed('Abbott_Impersonation::login_button');
            foreach ($dataSource['data']['items'] as &$item) {
                $storeId = $this->getStoreID($item['created_in'], $item['website_id'][0]);
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'impersonation/login/login',
                        ['id' => $item['entity_id'], 'store' => $storeId]
                    ),
                    'label' => __('Login As Customer'),
                    'hidden' => $hidden,
                    '__disableTmpl' => true,
                    'target' => '_blank'
                ];
            }
        }

        return $dataSource;
    }

    public function getStoreID($storeName, $websiteId)
    {
        $allstores = $this->storeRepository->getList();
        $storeId = '';

        foreach ($allstores as $store) {
            if ($store["name"] == $storeName && $store["website_id"] == $websiteId) {
                $storeId = $store["store_id"];
            }
             return $storeId;
        }
    }
}
