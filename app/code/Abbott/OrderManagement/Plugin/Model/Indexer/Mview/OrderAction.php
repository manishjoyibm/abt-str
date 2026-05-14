<?php

declare(strict_types=1);

namespace Abbott\OrderManagement\Plugin\Model\Indexer\Mview;

use Abbott\OrderManagement\Helper\Data as OrderManagementHelper;

class OrderAction
{
  public $helper;
    /**
   * OrderAction constructor.
   *
   * @param OrderManagementHelper $helper
   */
    public function __construct(
        OrderManagementHelper $helper
    ) {
        $this->helper = $helper;
    }

    public function aroundExecute(\Amasty\Orderattr\Model\Indexer\Mview\OrderAction $subject, callable $proceed)
    {
        if ($this->helper->isAmastyGridIndexerEnabled()) {
            $proceed();
        }
    }
}
