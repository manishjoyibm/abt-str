<?php

namespace Abbott\OrderManagement\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

class Forms extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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
        $form = $productEntity->getData('product_form');
        return ($form == 'null') ? "No" : $form;
    }
}
