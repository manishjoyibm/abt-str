<?php

namespace Abbott\Checkout\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Customer\Model\Session as CustomerSession;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

class UpdateCartItemPlugin
{
    const BRAND = 'Metabolics';
    const AVAILABLE_FOR_CALL = 1;
    const LEVEL = 'Level1';

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     *
     * @var \Abbott\Checkout\Helper\Data
     */
    protected $dataHelper;

    protected $customerSession;

    protected $metabolicData;


    /**
     *
     * @param GetCartForUser $getCartForUser
     * @param \Abbott\Checkout\Helper\Data $dataHelper
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        \Abbott\Checkout\Helper\Data $dataHelper,
        CustomerSession $customerSession,
        MetabolicData $metabolicData
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
        $this->metabolicData = $metabolicData;
    }

    public function beforeResolve(
        \Magento\QuoteGraphQl\Model\Resolver\UpdateCartItems $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($this->dataHelper->isEnabledQuantityValidation()) {

            $maskedCartId = $args['input']['cart_id'];
            $cartItems = $args['input']['cart_items'];
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
            foreach ($cartItems as $item) {
                $itemId = (int)$item['cart_item_id'];
                $cartItem = $cart->getItemById($itemId);

                if ($this->dataHelper->getSubscriptionOption($cartItem)) {
                    $productId = $cartItem->getProductId();
                    $qty = $item['quantity'];
                    $isValidQty = $this->dataHelper->validateProductQuantity($productId, $qty, '', $storeId);
                    if (!empty($isValidQty)) {
                        throw new GraphQlInputException(__($isValidQty));
                    }
                }

                // check allowed qty for metabolic products
                $customerEmail = $this->customerSession->getCustomer()->getEmail();
                if (($customerEmail != null) &&
                    (
                        $this->metabolicData->getLevelAttributeLabel(
                            $cartItem->getProduct()->getSku()
                        ) == self::LEVEL
                    ) &&
                    ($cartItem->getOrderOnCall() ==
                        self::AVAILABLE_FOR_CALL)
                ) {
                    $sku = $cartItem->getProduct()->getSku();
                    $qty = $item['quantity'];
                    $isValidQty = $this->dataHelper->validateProductQuantityForMetabolic($sku, $qty, $customerEmail);
                    if (!empty($isValidQty)) {
                        throw new GraphQlInputException(__($isValidQty));
                    }
                }
            }
        }
    }
}
