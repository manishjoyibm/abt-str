<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Entity\EntityData\Converter;

use Amasty\Orderattr\Model\ResourceModel\Entity\EntityData\Converter\GetConvertibleAttributeCodes;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CanConvertAttributeValue
{
    /**
     * @var GetConvertibleAttributeCodes
     */
    private $getConvertibleAttributeCodes;

    /**
     * @var string[]
     */
    private $convertibleFrontendInputs;

    /**
     * @var string[]
     */
    private $cachedAttributeCodes;

    public function __construct(
        GetConvertibleAttributeCodes $getConvertibleAttributeCodes,
        array $convertibleFrontendInputs = ['select', 'multiselect', 'radios', 'checkboxes']
    ) {
        $this->getConvertibleAttributeCodes = $getConvertibleAttributeCodes;
        $this->convertibleFrontendInputs = $convertibleFrontendInputs;
    }

    public function execute(string $attributeCode): bool
    {
        if ($this->cachedAttributeCodes === null) {
            $this->cachedAttributeCodes = $this->getConvertibleAttributeCodes->execute(
                $this->convertibleFrontendInputs
            );
        }

        return in_array($attributeCode, $this->cachedAttributeCodes);
    }
}
