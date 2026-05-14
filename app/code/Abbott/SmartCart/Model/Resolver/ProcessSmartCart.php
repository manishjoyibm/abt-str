<?php


namespace Abbott\SmartCart\Model\Resolver;


use Abbott\SmartCart\Api\SmartCartRepositoryInterface;
use Abbott\SmartCart\Model\SmartCart;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForGuest;
use Magento\SalesRule\Model\RuleFactory;

class ProcessSmartCart implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepositoryInterface;
    /**
     * @var CartManagementInterface
     */
    private $cartManagementInterface;
    /**
     * @var \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;
    /**
     * @var CreateEmptyCartForCustomer
     */
    private $createEmptyCartForCustomer;
    /**
     * @var CreateEmptyCartForGuest
     */
    private $createEmptyCartForGuest;
    /**
     * @var SmartCartRepositoryInterface
     */
    private $smartCartRepository;
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var RuleFactory
     */
    private $ruleFactory;
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;


    /**
     * ProcessSmartCart constructor.
     * @param CartRepositoryInterface $cartRepositoryInterface
     * @param CartManagementInterface $cartManagementInterface
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param SmartCartRepositoryInterface $smartCartRepository
     * @param QuoteFactory $quoteFactory
     * @param ProductFactory $productFactory
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param CreateEmptyCartForGuest $createEmptyCartForGuest
     * @param RuleFactory $ruleFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepositoryInterface,
        CartManagementInterface $cartManagementInterface,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        SmartCartRepositoryInterface $smartCartRepository,
        QuoteFactory $quoteFactory,
        ProductFactory $productFactory,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        CreateEmptyCartForGuest $createEmptyCartForGuest,
        RuleFactory $ruleFactory,
        CustomerRepositoryInterface $customerRepository
    ) {

        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->createEmptyCartForGuest = $createEmptyCartForGuest;
        $this->smartCartRepository = $smartCartRepository;
        $this->quoteFactory = $quoteFactory;
        $this->productFactory = $productFactory;
        $this->ruleFactory = $ruleFactory;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $code = $args['input']['code'] ?? null;

        if(!$code) {
            throw new GraphQlInputException(__("\"code\" is a required field"));
        }
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        try {
            $smartCart = $this->smartCartRepository->getSmartCartByCode($code, $storeId);
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__("We were not able to process this cart"));
        }
        $customerId = $context->getUserId();
        $predefinedMaskedQuoteId = null;

        if(0 === $customerId || null === $customerId) {
            $maskedQuoteId = $this->createEmptyCartForGuest->execute($predefinedMaskedQuoteId);
            $quote = $this->quoteFactory->create()->load($this->maskedQuoteIdToQuoteId->execute($maskedQuoteId));

        } else {
            $quote = $this->cartManagementInterface->getCartForCustomer($customerId);
            $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute($quote->getId());
        }

        if(!empty($smartCart->getProducts())) {
            $products = $smartCart->getProducts();
        }
        try {
            /** @var \Magento\Quote\Model\Quote $quote */
            foreach ($products as $productId => $productQty) {
                $product = $this->productFactory->create()->load($productId);
                if(!$quote->getItemByProduct($product)) {
                    $quote->addProduct($product, $productQty);
                }
            }
            if($ruleId = $smartCart->getDiscountRuleId()){
                $rule = $this->ruleFactory->create()->load($ruleId);
                $quote->setCouponCode($rule->getCouponCode());
            }
            $quote->setIsSmartCart(true);
            $quote->collectTotals()->save();
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__("We were not able to process this cart"));
        }


        return ["cart_mask_id" => $maskedQuoteId];

    }
}
