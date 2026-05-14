<?php

namespace Abbott\Catalog\Plugin\AdditionalAttributes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Abbott\AdditionalAttributes\Model\Resolver\Product\Qty;

/**
 * Class QtyPlugin
 * @package Abbott\Catalog\Plugin\AdditionalAttributes\Model\Resolver
 */
class QtyPlugin
{

    /**
     * @var \Abbott\Catalog\Helper\Data
     */
    private $helper;

    /**
     * QtyPlugin constructor.
     * @param \Abbott\Catalog\Helper\Data $helper
     */
    public function __construct(\Abbott\Catalog\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Qty $subject
     * @param $result
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        Qty $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($this->helper->isDisableSaleEnabled()) {
            $product = $value[Qty::MODEL];
            if ($product->getDisableSale()) {
                $result['qty'] = 0;
                $result['status'] = "OUT_OF_STOCK";
            }
        }
        return $result;
    }
}
