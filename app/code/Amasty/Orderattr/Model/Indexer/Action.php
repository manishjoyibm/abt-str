<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Indexer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\Search\Request\Dimension;

class Action extends \Magento\Framework\Indexer\Action\Entity
{
    /**
     * Prepare select query
     *
     * @param array|int|null $ids
     * @return SourceProviderInterface
     */
    protected function prepareDataSource(array $ids = [])
    {
        $collection = $this->createResultCollection();
        if (!empty($ids)) {
            $collection->addFieldToFilter($this->getPrimaryResource()->getRowIdFieldName(), $ids);
        }

        return $collection;
    }

    public function executeFull(): void
    {
        $dimension = new Dimension('replica', '_replica');

        $this->prepareFields();
        $indexer = $this->getSaveHandler();
        $indexer->cleanIndex([$dimension]);
        $indexer->saveIndex([$dimension], $this->prepareDataSource());

        // use OM to avoid loading dependencies of parent class
        $tableSwitcher = ObjectManager::getInstance()->get(GridTableSwitcher::class);
        $tableSwitcher->switchTables();
    }
}
