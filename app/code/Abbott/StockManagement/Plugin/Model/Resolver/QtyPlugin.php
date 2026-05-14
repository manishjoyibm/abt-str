<?php

namespace Abbott\StockManagement\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Abbott\AdditionalAttributes\Model\Resolver\Product\Qty;

class QtyPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    const BACKORDER = 4;
    /**
     * @var \Abbott\StockManagement\Helper\Data
     */
    private $helper;

    /**
     * QtyPlugin constructor.
     * @param \Abbott\StockManagement\Helper\Data $helper
     */
    public function __construct(
        \Abbott\StockManagement\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
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
        $product = $value[Qty::MODEL];
        if ($this->helper->getConfigValue() &&
            $this->helper->checkStock($product) == self::BACKORDER &&
            $result['status'] == 'IN_STOCK') {
            $thresold = $product->getResource()->getAttributeRawValue(
                $product->getId(),
                'threshold',
                $product->getStoreId()
            );
            if ($result['qty'] <= $thresold) {
                $result['qty'] = 0;
                $result['status'] = "OUT_OF_STOCK";
            }
        }
        return $result;
    }
}
