<?php

namespace Abbott\ProgressiveDiscount\Controller\Cart;

use Magento\Catalog\Helper\Image;
use Magento\Checkout\Model\Session;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Controller for processing add to cart action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var \Magento\Checkout\CustomerData\Cart
     */
    public $sectionCart;
    /**
     * @var Image
     */
    public $imageHelper;
    public $sectionPool;
    public const MESSAGES = 'messages';

    /**
     * Constructor function
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param \Magento\Checkout\CustomerData\Cart $sectionCart
     * @param Image $imageHelper
     * @param SectionPoolInterface $sectionPool
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param RequestQuantityProcessor $quantityProcessor
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        \Magento\Checkout\CustomerData\Cart $sectionCart,
        Image $imageHelper,
        SectionPoolInterface $sectionPool,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        RequestQuantityProcessor $quantityProcessor
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository,
            $quantityProcessor
        );
        $this->sectionCart = $sectionCart;
        $this->imageHelper = $imageHelper;
        $this->sectionPool = $sectionPool;
        $this->productRepository = $productRepository;
    }

    /**
     * Resolve response
     *
     * @param $backUrl
     * @param $product
     * @return ResponseInterface|Redirect|ResultInterface|void
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }
        $result = [];
        $sectionNames[] = 'cart';
        $sectionNames[] = self::MESSAGES;
        $response = $this->sectionPool->getSectionsData($sectionNames, true);
        $result[self::MESSAGES] = $response[self::MESSAGES];
        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            } elseif ($product && $product->getIsSalable() && !$this->cart->getQuote()->getHasError()) {
                $result['product'] = [
                    'success' => true,
                    'img' => $this->imageHelper->init($product, 'product_base_image')->getUrl(),
                    'id' => $product->getId(),
                    'url' => $product->getProductUrl(),
                    'price' =>  number_format($product->getPrice(), '2', '.', ',')
                ];
                $result['cart'] = $response['cart'];
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
