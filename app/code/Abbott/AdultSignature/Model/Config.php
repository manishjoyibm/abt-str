<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

/**
 * Provides checkout configuration values for the adult signature module.
 *
 * @category  Abbott
 * @package   Abbott_AdultSignature
 */
class Config
{
    private const XPATH_MESSAGE = 'abbott_adult_signature/general/popup_message';
     private const XPATH_ADMIN_MESSAGE = 'abbott_adult_signature/general/admin_message';
    private const XPATH_ENABLED = 'abbott_adult_signature/general/enabled';

    /** @var ScopeConfigInterface */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig Scope configuration
     */
    private RegionCollectionFactory $regionCollectionFactory;
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RegionCollectionFactory $regionCollectionFactory
        )
    {
        $this->scopeConfig = $scopeConfig;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * Get the enable value
     *
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENABLED, ScopeInterface::SCOPE_STORE);
    
    }
    /**
     * Get the Popup message value
     *
     */
     public function getPopMessage()
    {
        return  (string)$this->scopeConfig->getValue(self::XPATH_MESSAGE, ScopeInterface::SCOPE_STORE);
    
    }

    /**
     * Get the Admin message value
     *
     */
     public function getAdminMessage()
    {
        return  (string)$this->scopeConfig->getValue(self::XPATH_ADMIN_MESSAGE, ScopeInterface::SCOPE_STORE);
    
    }
    
    /**
     * Resolves an array of region IDs to [ [id, code, name, country_id], ... ].
     * Uses a single collection query for performance.
     *
     * @param int[] $regionIds
     * @return array<int, array{id:int, code:string, name:string, country_id:string}>
     */
   public function resolveRegions(array $regionIds): array
        {
            if (empty($regionIds)) {
                return [];
            }

            $collection = $this->regionCollectionFactory->create();
            $collection->addCountryFilter('US');

            // FIX: Prefix the field with 'main_table.' to avoid the SQL ambiguity error
            $collection->addFieldToFilter('main_table.region_id', ['in' => $regionIds]);

            $out = [];
            foreach ($collection as $region) {
                $out[] = [
                    'id'         => (int)$region->getId(),
                    'code'       => (string)$region->getCode(),
                    'name'       => (string)$region->getName(),
                    'country_id' => (string)$region->getCountryId(),
                ];
            }
            usort($out, static fn($a, $b) => $a['id'] <=> $b['id']);
            return $out;
        }
}
