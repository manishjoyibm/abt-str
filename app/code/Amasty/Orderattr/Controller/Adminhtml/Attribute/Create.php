<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Controller\Adminhtml\Attribute;

class Create extends \Amasty\Orderattr\Controller\Adminhtml\Attribute
{
    /**
     * @see \Amasty\Orderattr\Controller\Adminhtml\Attribute\Edit::execute
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        return $this->_forward('edit');
    }
}
