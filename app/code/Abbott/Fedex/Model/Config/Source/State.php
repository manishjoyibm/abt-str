<?php
namespace Abbott\Fedex\Model\Config\Source;

use Magento\Directory\Model\Country;

class State
{
    /**
     * Code for filtering regions
     */
    private const COUNTRY_CODE = "US";

    /**
     * @var Country
     */
    protected Country $country;

    /**
     * State constructor.
     * @param Country $country
     */
    public function __construct(
        Country $country
    ) {
        $this->country = $country;
    }

    /**
     * Method toOptionArray
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $this->country->loadByCode(self::COUNTRY_CODE);
        $regions = $this->country->getRegions();
        $regionsOptions = $regions->toOptionArray();
        array_shift($regionsOptions);
        return $regionsOptions;
    }
}
