<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;

/**
 * Class Configurable
 * @package Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type
 */
class Configurable implements HandlerInterface
{
    /**
     * @var LinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Generic
     */
    private $genericHandler;

    /**
     * @param LinkManagementInterface $linkManagement
     * @param ProductRepositoryInterface $productRepository
     * @param Generic $genericHandler
     */
    public function __construct(
        LinkManagementInterface $linkManagement,
        ProductRepositoryInterface $productRepository,
        Generic $genericHandler
    ) {
        $this->linkManagement = $linkManagement;
        $this->productRepository = $productRepository;
        $this->genericHandler = $genericHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function check($product, $subscriptionOnly = false)
    {
        $childProducts = $this->linkManagement->getChildren($product->getData('sku'));
        foreach ($childProducts as $childProduct) {
            $childProduct = $this->productRepository->get($childProduct->getSku());
            if ($this->genericHandler->check($childProduct, $subscriptionOnly)) {
                return true;
            }
        }
        return false;
    }
}
