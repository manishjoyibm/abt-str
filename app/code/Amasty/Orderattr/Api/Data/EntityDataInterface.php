<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Api\Data;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;

interface EntityDataInterface extends CheckoutEntityInterface, CustomAttributesDataInterface
{

}
