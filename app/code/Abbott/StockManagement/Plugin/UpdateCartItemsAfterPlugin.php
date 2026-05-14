<?php

namespace Abbott\StockManagement\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\UpdateCartItems;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

class UpdateCartItemsAfterPlugin
{

    public const ALLOW_BACKORDER = 2;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     *
     * @var \Abbott\StockManagement\Helper\Data
     */
    protected $dataHelper;


    protected $checkoutDataHelper;


    protected $productRepository;

    /**
     *
     * @param GetCartForUser $getCartForUser
     * @param \Abbott\StockManagement\Helper\Data $dataHelper
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        \Abbott\StockManagement\Helper\Data $dataHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository

    ) {
        $this->getCartForUser = $getCartForUser;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * @param UpdateCartItems $subject
     * @param $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     */
    public function afterResolve(UpdateCartItems $subject, $result, Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $maskedCartId = $args['input']['cart_id'];
        $cartItems = $args['input']['cart_items'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        foreach ($cartItems as $item) {
            $itemId = (int)$item['cart_item_id'];
            $cartItem = $cart->getItemById($itemId);
            $productId = $cartItem->getProductId();
            $is_subscription = 0;
            if ($cartItem->getData('aw_sarp_regular_row_total') != 0) {
                $is_subscription = 1;
            }
            $qty = $item['quantity'];
            $product = $this->productRepository->getById(
                $productId,
                false,
                $cartItem->getStoreId()
            );
            $saleableQty = $product->getData()['quantity_and_stock_status']['qty'];
            if (!$is_subscription && $this->dataHelper->getConfigValue() && $this->dataHelper->checkStock($product) == self::ALLOW_BACKORDER && $product->getData()['quantity_and_stock_status']['is_in_stock']) {
                if ($qty > $saleableQty) {
                    $qtyMsg = ($saleableQty < 0) ? $qty : ($qty - $saleableQty);
                    $message = __(
                        "We don't have as many '%name' as you requested, but we'll back order the remaining '%qty'.",
                        ['name' => $product->getName(), 'qty' => $qtyMsg]
                    );
                    $result['cart']['success'] = [$message];
                }
            }
        }
        return $result;
    }
}
