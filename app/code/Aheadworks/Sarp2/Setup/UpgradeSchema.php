<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Setup;

use Aheadworks\Sarp2\Setup\Updater\Shema\Updater;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Aheadworks\Sarp2\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Updater
     */
    private $updater;

    /**
     * @param Updater $updater
     */
    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->updater->upgradeTo220($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->updater->upgradeTo230($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->updater->upgradeTo240($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.5.0', '<')) {
            $this->updater->upgradeTo250($setup);
        }
    }
}
