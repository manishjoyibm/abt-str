<?php

namespace Abbott\MyAccount\Plugin\Model\Customer;

use Magento\Directory\Model\RegionFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

class SetData
{
    public $regionFactory;
    /**
     * Construct function
     *
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        RegionFactory $regionFactory
    ) {
        $this->regionFactory = $regionFactory;
    }

    /**
     * BeforeSave function
     *
     * @param CustomerRepository $subject
     * @param $customer
     * @return void
     */
    public function beforeSave(
        CustomerRepository $subject,
        $customer
    ) {
        if (is_array($customer->getAddresses())) {
            foreach ($customer->getAddresses() as $add) {
                if (!$add->getRegion()->getRegionId() && $add->getRegion()->getRegionCode() && $add->getCountryId()) {
                    $region = $this->regionFactory->create()->loadByCode(
                        $add->getRegion()->getRegionCode(),
                        $add->getCountryId()
                    );
                    if ($region->getRegionId()) {
                        $regionId = $region->getRegionId();
                        $regionName = $region->getName();
                        $add->getRegion()->setRegionId($regionId);
                        $add->getRegion()->setRegion($regionName);
                    }
                }
            }
        }
    }
}
