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

class SubscriptionPrice implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    public $metadataPool;
    public $productFactory;
    public $planRepository;
    public $profileRepository;
    public $priceCalculation;
    public $provider;
    public $_catalogSession;
    /**
     *
     * @param \Abbott\PriceInvGql\Model\Resolver\MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Aheadworks\Sarp2\Api\PlanRepositoryInterface $planRepository
     */
    public function __construct(
        MetadataPool $metadataPool,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Aheadworks\Sarp2\Api\PlanRepositoryInterface $planRepository,
        \Aheadworks\Sarp2\Api\ProfileRepositoryInterface $profileRepository,
        \Abbott\PriceInvGql\Model\Product\Subscription\PriceCalculation  $priceCalculation,
        \Abbott\ProgressiveDiscount\Engine\Profile\PaymentsInfo\Provider $provider,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->metadataPool = $metadataPool;
        $this->productFactory = $productFactory;
        $this->planRepository = $planRepository;
        $this->profileRepository = $profileRepository;
        $this->priceCalculation = $priceCalculation;
        $this->provider = $provider;
        $this->_catalogSession = $catalogSession;
    }

    /**
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param type $context
     * @param \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info
     * @param array $value
     * @param array $args
     * @return type
     * @throws LocalizedException
     */
    public function resolve(
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $subscriptionPrice = 0;
        if (!empty($this->_catalogSession->getProfileIds())) {
            $discount = null;
            $isProgressive = false;
            $product = $value['model'];
            $productEntity = $this->productFactory->create();
            $productEntity->load($productEntity->getIdBySku($product->getData('sku')));

            $profile = $this->profileRepository->get($this->_catalogSession->getProfileIds());
            $profilePlanId = $profile->getPlanId();
            $newPlan = $this->planRepository->get($profilePlanId);
            if (!empty($newPlan)) {
                $isProgressive = $newPlan->getIsProgressive();
            }
            if ($isProgressive) {
                $discount = $this->provider->getDiscountByMonth($profile);
            }
            $subscriptionPrice = $this->priceCalculation->getAutoRegularPrice(
                $productEntity->getData('entity_id'),
                $profilePlanId,
                $discount
            );
        }
        return $subscriptionPrice;
    }

}
