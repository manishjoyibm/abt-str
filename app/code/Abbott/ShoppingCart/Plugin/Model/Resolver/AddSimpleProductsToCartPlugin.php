<?php

namespace Abbott\ShoppingCart\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;


class AddSimpleProductsToCartPlugin
{

    public $storeManager;
    public $productResource;
    public $shoppingCartHelper;
    public $checkoutHelper;
    public $ssmHelper;
    public $optionRepository;
    public $dataHelper;
    public $log;
    public $context;
	public $smHelper;

	public $productRepository;

	public const ALLOW_BACKORDER = 2;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Abbott\ShoppingCart\Helper\Data $helper,
		\Abbott\Checkout\Helper\Data $checkoutHelper,
		\Abbott\Strongmoms\Helper\Data $ssmHelper,
		\Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface $optionRepository,
		\Abbott\ProgressiveDiscount\Helper\Data $dataHelper,
		\Abbott\AwsLambda\Logger\Log $log,
		\Abbott\StockManagement\Helper\Data $smHelper,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->productResource = $productResource;
        $this->shoppingCartHelper = $helper;
		$this->checkoutHelper = $checkoutHelper;
		$this->ssmHelper = $ssmHelper;
		$this->optionRepository = $optionRepository;
		$this->dataHelper = $dataHelper;
		$this->log = $log;
		$this->smHelper = $smHelper;
		$this->productRepository = $productRepository;
    }

    public function aroundResolve(
        \Magento\QuoteGraphQl\Model\Resolver\AddSimpleProductsToCart $subject,
        \Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->shoppingCartHelper->checkSubscriptionId($args['input']['cart_items']);
        return $proceed($field, $context, $info, $value, $args);
    }

    public function afterResolve(
        \Magento\QuoteGraphQl\Model\Resolver\AddSimpleProductsToCart $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->context = $context;
        $cartItems = $args['input']['cart_items'];
        $messages = [];
        $message = '';
        $loggedIn = 0;
        if (true === $this->context->getExtensionAttributes()->getIsCustomer()) {
            //Customer is logged in
            $loggedIn = 1;
        }
        $this->log->writeLog("User logged in flag:".$loggedIn);
        foreach ($cartItems as $cartItem) {
            $this->log->writeLog("inside add simple product plugin:".print_r($cartItem['data'], true));
            if (array_key_exists('aw_sarp2_subscription_type', $cartItem['data'])) {
                if ($loggedIn) {
                    if (isset($cartItem['data']['aw_sarp2_subscription_type']) &&
                        $this->checkoutHelper->isSSMSubscriptionProgramEnabled() &&
                        $this->ssmHelper->isSSM()) {
                        //check for plan is progressive
                        $isProgressive = $this->checkIsProgressivePlan($cartItem['data']['aw_sarp2_subscription_type']);
                        if ($isProgressive) {
                            $message = $this->checkoutHelper->getSSMMinicartPrgMessage();
                        } else {
                            $message = $this->checkoutHelper->getSSMMinicartMessage();
                        }
                    } elseif (isset($cartItem['data']['aw_sarp2_subscription_type']) &&
                        $this->checkoutHelper->isSSMSubscriptionProgramEnabled() &&
                        !$this->ssmHelper->isSSM()) {
                        $isProgressive = $this->checkIsProgressivePlan($cartItem['data']['aw_sarp2_subscription_type']);
                        if ($isProgressive) {
                            $message = $this->checkoutHelper->getNonSSMMinicartPrgMessage();
                        } else {
                            $message = $this->checkoutHelper->getNonSSMMinicartMessage();
                        }
                    }
                } else {
                    //check for plan is progressive
                    $isProgressive = $this->checkIsProgressivePlan($cartItem['data']['aw_sarp2_subscription_type']);
                    if ($isProgressive) {
                        /*
                        * If x-id-token cookie exists
                        * then customer logged in AEM and IS-SSM user show the appropriate message
                        * else customer not logged in AEM show the appropriate message
                        */
                        $message = ($this->checkoutHelper->getXIdToken()) ?
                            $this->checkoutHelper->getSSMMinicartPrgMessage() :
                            $this->checkoutHelper->getGuestMinicartPrgMessage();
                    } else {
                        $message = ($this->checkoutHelper->getXIdToken()) ?
                            $this->checkoutHelper->getSSMMinicartMessage() :
                            $this->checkoutHelper->getGuestMinicartMessage();
                    }
                }

                if ($message) {
                    array_push($messages, $message);
                }
            }
            $product = $this->productResource;
            $productName = $product->getAttributeRawValue(
                $product->getIdBySku($cartItem['data']['sku']),
                'name',
                $this->storeManager->getStore()->getId()
            );
            $message = __(
                'You have added %1 to your cart.',
                $productName
            );
            array_push($messages, $message);
			$productId = $product->getIdBySku($cartItem['data']['sku']);
			$productRepo = $this->productRepository->getById(
				$productId,
				false,
				$this->storeManager->getStore()->getId()
			);
			$saleableQty = $productRepo->getData()['quantity_and_stock_status']['qty'];
			$itemQty = 0;
			foreach ($result['cart'] as $cartProductData)
			{
				foreach ($cartProductData['items'] as $item)
                {
                 $cartProductId = $item->getData('product_id');
				 if ($cartProductId == $productId) {
					$itemQty = $item->getData('qty');
				}
                }
				
			}
			$itemQty = ($itemQty) ? $itemQty : $cartItem['data']['quantity'];
			if (
				!array_key_exists('aw_sarp2_subscription_type', $cartItem['data'])
				&& $this->smHelper->checkStock($productRepo) == self::ALLOW_BACKORDER
				&& $productRepo->getData()['quantity_and_stock_status']['is_in_stock']
				&& ($itemQty > $saleableQty)
			) {
					$qty = ($saleableQty < 0) ? $itemQty : ($itemQty - $saleableQty);
					$message = __(
						"We don't have as many '%name' as you requested, but we'll back order the remaining '%qty'.",
						['name' => $productName, 'qty' => $qty]
					);
				array_push($messages, $message);
			}
        }
		$stringMessage = implode(" ", $messages);
        $result['cart']['success'] = [$stringMessage];
        return $result;
    }

	private function checkIsProgressivePlan($optionId)
    {
		 if (!empty($optionId)) {
                $option = $this->optionRepository->get($optionId);
                $planId = $option->getPlanId();
                if(!empty($planId)){
                    return $this->dataHelper->getIsProgressive($planId);
                }
            }
    }
}
