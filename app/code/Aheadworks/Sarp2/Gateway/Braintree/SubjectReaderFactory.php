<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubjectReaderFactory
 * @package Aheadworks\Sarp2\Gateway\Braintree
 */
class SubjectReaderFactory
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
     * Get subjectReader object instance
     *
     * @return \PayPal\Braintree\Gateway\Helper\SubjectReader|\PayPal\Braintree\Gateway\SubjectReader
     */
    public function getInstance()
    {
        if ($this->versionChecker->isModuleVersion3X()) {
            $instance = $this->objectManager->get(\PayPal\Braintree\Gateway\Helper\SubjectReader::class);
        } else {
            $instance = $this->objectManager->get(\PayPal\Braintree\Gateway\SubjectReader::class);
        }

        return $instance;
    }
}
