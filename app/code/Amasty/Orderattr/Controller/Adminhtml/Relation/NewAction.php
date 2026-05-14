<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

class NewAction extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
