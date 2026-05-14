<?php

namespace Abbott\Checkout\CustomerData;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Abbott\MyAccount\Helper\Data as MyAccountData;

class DirectoryData extends \Magento\Checkout\CustomerData\DirectoryData
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * Cache indentifier
     */
    protected $identifier = "directory_data_cache";

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\App\Cache
     */
    protected $cache;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\App\Cache $cache
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Cache $cache,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    protected function getCacheId()
    {
        $storeId = $this->storeManager->getStore()->getId();

        return $storeId . "_" . $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        // check cache exists.. return from cache if exists..
        if ($this->storeManager->getStore()->getCode() == MyAccountData::NEW_SIM_STORE_CODE) {
            $cacheData = $this->cache->load($this->getCacheId());
            if ($cacheData) {
                return $this->serializer->unserialize($cacheData, true);
            }
        }

        $output = [];
        $regionsData = $this->directoryHelper->getRegionData();
        /**
         * @var string $code
         * @var \Magento\Directory\Model\Country $data
         */
        foreach ($this->directoryHelper->getCountryCollection() as $code => $data) {
            $output[$code]['name'] = $data->getName();
            if (array_key_exists($code, $regionsData)) {
                foreach ($regionsData[$code] as $key => $region) {
                    $output[$code]['regions'][$key]['code'] = $region['code'];
                    $output[$code]['regions'][$key]['name'] = $region['name'];
                }
            }
        }

        if ($this->storeManager->getStore()->getCode() == MyAccountData::NEW_SIM_STORE_CODE) {
            $cacheData = $this->serializer->serialize($output);
            // create cache before return..
            $this->cache->save($cacheData, $this->getCacheId(), ['DirectoryData'], 86400);
        }

        return $output;
    }
}
