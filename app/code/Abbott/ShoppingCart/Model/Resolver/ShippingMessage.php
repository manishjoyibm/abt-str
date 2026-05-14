<?php

namespace Abbott\ShoppingCart\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Abbott\ShoppingCart\Helper\Data as dataHelper;

class ShippingMessage implements ResolverInterface
{
    public const SUB_TYPE = 'aw_sarp2_subscription_type';

    /**
     * @var RequestContentInterface
     */
    protected $request;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    public function __construct(
        \Magento\Framework\App\RequestContentInterface $request,
        DataHelper $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->request = $request;
    }

    /**
     * Resolver
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
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
        $response = [];
        $cart = $value['model'];
        $isSubscriptionProduct = 0;
        if (strpos($this->request->getContent(), self::SUB_TYPE) !== false) {
            $isSubscriptionProduct = 1;
        }
        $total = $cart->getSubtotal();
        $items_count = $cart->getItemsCount();
        $response = $this->dataHelper->getCartShippingDetails($total, $isSubscriptionProduct, $items_count);
        return $response;
    }
}
