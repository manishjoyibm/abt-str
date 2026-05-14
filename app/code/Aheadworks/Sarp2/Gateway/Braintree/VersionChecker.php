<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree;

use Magento\Framework\Module\ModuleListInterface;

/**
 * Class VersionChecker
 * @package Aheadworks\Sarp2\Gateway\Braintree
 */
class VersionChecker
{
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Get subjectReader object instance
     *
     * @return bool
     */
    public function isModuleVersion3X()
    {
        $moduleInfo = $this->moduleList->getOne('PayPal_Braintree');
        return version_compare($moduleInfo['setup_version'], '3.0.0', '>=');
    }
}
