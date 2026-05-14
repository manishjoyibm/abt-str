<?php

declare(strict_types=1);

namespace Abbott\AdditionalAttributes\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class Subscription implements ResolverInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    public $metadataPool;
    public $productFactory;
    public $planRepository;
    public function __construct(
        MetadataPool $metadataPool,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Aheadworks\Sarp2\Api\PlanRepositoryInterface $planRepository
    ) {
        $this->metadataPool = $metadataPool;
        $this->productFactory = $productFactory;
        $this->planRepository = $planRepository;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $plan[] = null;
        $product = $value['model'];
        $productEntity = $this->productFactory->create();
        $productEntity->load($productEntity->getIdBySku($product->getData('sku')));
        foreach ($productEntity->getData('aw_sarp2_subscription_options') as $subscriptionOption) {
            $planDetails = $this->planRepository->get($subscriptionOption['plan_id']);
            $planName = $planDetails['name'];
            $plan['option_label'] = $planName;
            $planOptionId = $subscriptionOption['option_id'];
            $plan['option_id'] = $planOptionId;
        }
        return $plan;
    }
}
