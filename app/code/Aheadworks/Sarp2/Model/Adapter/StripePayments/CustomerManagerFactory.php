<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Adapter\StripePayments;

use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Sarp2\Model\ThirdPartyModule\Manager;

/**
 * Class CustomerManagerFactory
 *
 * @package Aheadworks\Sarp2\Model\Adapter\StripePayments
 */
class CustomerManagerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Manager
     */
    private $thirdPartyModuleManager;

    /**
     * @var mixed|null
     */
    private $customerManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $thirdPartyModuleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $thirdPartyModuleManager
    ) {
        $this->objectManager = $objectManager;
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
    }

    /**
     * Retrieve customer manager object if corresponding module is enabled
     *
     * @return mixed|null
     */
    public function getCustomerManager()
    {
        if (empty($this->customerManager)) {
            if ($this->thirdPartyModuleManager->isStripeModuleEnabled()) {
                $this->customerManager = $this->objectManager->get(
                    \StripeIntegration\Payments\Model\StripeCustomer::class
                );
            }
        }

        return $this->customerManager;
    }
}
