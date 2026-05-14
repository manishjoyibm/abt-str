<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree\Model\Adapter;

use Aheadworks\Sarp2\Gateway\Braintree\VersionChecker;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class BraintreeAdapterFactory
 * @package Aheadworks\Sarp2\Gateway\Braintree\Model\Adapter
 */
class BraintreeAdapterFactory
{
    /**
     * @var VersionChecker
     */
    private $versionChecker;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param VersionChecker $versionChecker
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        VersionChecker $versionChecker,
        ObjectManagerInterface $objectManager
    ) {
        $this->versionChecker = $versionChecker;
        $this->objectManager = $objectManager;
    }

    /**
     * Resolve adapter instance depending on Braintree version
     *
     * @param int|null $storeId
     * @return \PayPal\Braintree\Model\Adapter\BraintreeAdapter
     */
    public function getInstance($storeId = null)
    {
        if (!$this->versionChecker->isModuleVersion3X()) {
            $adapterFactory = $this->objectManager->get(
                \PayPal\Braintree\Model\Adapter\BraintreeAdapterFactory::class
            );
            $adapter = $adapterFactory->create($storeId);
        } else {
            $adapter = $this->objectManager->create(
                \PayPal\Braintree\Model\Adapter\BraintreeAdapter::class
            );
        }

        return $adapter;
    }
}
