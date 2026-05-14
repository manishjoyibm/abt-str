<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Gateway\Braintree\VersionChecker;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class StoreConfigBuilder
 * @package Aheadworks\Sarp2\Gateway\Braintree\Request
 *
 * Compatibility with Braintree 2.X.
 */
class StoreConfigBuilder implements BuilderInterface
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
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [];
        if (!$this->versionChecker->isModuleVersion3X()) {
            $storeConfigBuilder = $this->objectManager->get(
                \PayPal\Braintree\Gateway\Request\StoreConfigBuilder::class
            );
            $result = $storeConfigBuilder->build($buildSubject);
        }

        return $result;
    }
}
