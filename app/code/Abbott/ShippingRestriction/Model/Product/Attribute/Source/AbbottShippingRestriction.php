<?php

namespace Abbott\ShippingRestriction\Model\Product\Attribute\Source;

use Magento\Directory\Model\RegionFactory;

class AbbottShippingRestriction extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $optionFactory;
    private $directoryData;
    public const COUNTRY = 'US';

    /**
     * Construct function
     *
     * @param RegionFactory $directoryData
     */
    public function __construct(
        RegionFactory $directoryData
    ) {
        $this->directoryData = $directoryData;
    }

    /**
     * GetAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $states = $this->directoryData->create()->getCollection()->addFieldToFilter('country_id', self::COUNTRY);
        foreach ($states as $state) {
            $this->_options[] = [
                'value' => $state['region_id'],
                'label' => $state['name'],
            ];
        }
        return $this->_options;
    }
}
