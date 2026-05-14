<?php
namespace Abbott\MetabolicOrdering\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Abbott\MetabolicOrdering\Helper\Data;

class FilteredProducts implements ArrayInterface
{
    /**
     * @var helper
     */
    protected $helper;
    /**
     * Constructor
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * Return array of Metabolic sku
     *
     * @return $result
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->helper->filterProducts() as $value => $label) {
            $result[] = [
                 'value' => $label,
                 'label' => $label,
             ];
        }

        return $result;
    }
}
