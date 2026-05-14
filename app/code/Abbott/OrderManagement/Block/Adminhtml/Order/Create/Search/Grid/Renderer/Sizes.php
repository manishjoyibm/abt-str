<?php

namespace Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

class Sizes extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    public $productRepository;
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $productEntity = $this->productRepository->getById($row->getId());
        $case = $productEntity->getData('case_of_product');
        return ($case == 'null') ? "No" : $case;
    }
}
