<?php

namespace Abbott\Sales\Block\Order\Email\Items\Order;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;

class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
      /**
       * @var ProductRepositoryInterface
       */
      protected $productRepository;

      /**
       * Construct function
       *
       * @param Context $context
       * @param ProductRepositoryInterface $productRepository
       * @param array $data
       */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
          $this->productRepository = $productRepository;
          parent::__construct($context, $data);
    }

      /**
       * GetProductBySku function
       *
       * @param string $sku
       * @return ProductInterface
       * @throws NoSuchEntityException
       */
    public function getProductBySku($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__('Such product doesn\'t exist'));
        }
        return $product;
    }
}
