<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Api\Data;

use Magento\Framework\Api\AttributeInterface;

interface AttributeValueInterface extends AttributeInterface
{
    /**
     * @param string|null $label
     * @return $this
     */
    public function setLabel(?string $label);

    /**
     * @return string|null
     */
    public function getLabel(): ?string;
}
