<?php

declare(strict_types=1);

namespace Abbott\OrderManagement\Plugin\Model\Indexer;

use Abbott\OrderManagement\Helper\Data as OrderManagementHelper;

class Action
{
  public $helper;
    /**
   * Action constructor.
   *
   * @param OrderManagementHelper $helper
   */
    public function __construct(
        OrderManagementHelper $helper
    ) {
        $this->helper = $helper;
    }

    public function aroundExecuteFull(\Amasty\Orderattr\Model\Indexer\Action $subject, callable $proceed)
    {
        if ($this->helper->isAmastyGridIndexerEnabled()) {
            $proceed();
        }
    }
}
