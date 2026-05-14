<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Entity\EntityData;

use Amasty\Orderattr\Api\Data\AttributeValueInterface;

class AttributeValue extends \Magento\Framework\Api\AttributeValue implements AttributeValueInterface
{
    public const LABEL = 'label';

    public function setLabel(?string $label)
    {
        return $this->setData(self::LABEL, $label);
    }

    public function getLabel(): ?string
    {
        return $this->_get(self::LABEL);
    }
}
