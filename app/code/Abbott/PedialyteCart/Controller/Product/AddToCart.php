<?php
namespace Abbott\PedialyteCart\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Abbott\PedialyteCart\Helper\Data as PedialyteCartHelper;
use Abbott\PedialyteCart\Logger\Logger;

class AddToCart extends Action
{
    protected $cart;
    protected $productRepository;
    protected $quoteIdMaskFactory;
    protected $accountHelper;
    protected $pedialyteCartHelper;
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManagerInterface;
    /**
     * @var Logger
     */
    private $logger;

    public const CHECKOUT_CART_INDEX_REDIRECT = '/checkout/cart/index/';

    public function __construct(
        Context $context,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManagerInterface,
        AccountHelper $accountHelper,
        PedialyteCartHelper $pedialyteCartHelper,
        Logger $logger,
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManagerInterface = $cookieManagerInterface;
        $this->accountHelper = $accountHelper;
        $this->pedialyteCartHelper = $pedialyteCartHelper;
        $this->logger = $logger;
    }

    /**
     * Set the cookie
     * @param string $key
     * @param string $value
     * @param \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $metaData
     *
     */
    public function setCookie($key, $value, $metaData): void
    {
        $this->cookieManagerInterface->setPublicCookie(
            $key,
            $value,
            $metaData
        );
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->pedialyteCartHelper->getModuleEnable()) {
            $sku = $this->getRequest()->getParam('sku');
            $qtyParam = $this->getRequest()->getParam('qty');
            $queryString = $this->getRequest()->getServer('QUERY_STRING');
            $params = $this->getRequest()->getParams();
            $this->logger->info(print_r($params, true));
            $this->logger->info(print_r($queryString, true));

            if (isset($qtyParam) && $qtyParam > 0) {
                $qty = $qtyParam;
            } else {
                $qty = 1;
            }

            $mboSkuList = $this->pedialyteCartHelper->getMboSkuList();

            if (!in_array($sku, $mboSkuList)) {
                $this->messageManager->addErrorMessage(__('Unable to add product to cart.'));
                $this->logger->info($sku. "is not found in MBO sku list");
                return $this->_redirect(self::CHECKOUT_CART_INDEX_REDIRECT);
            }

            try {
                $product = $this->productRepository->get($sku);
                $this->cart->addProduct($product, $qty);
                $this->cart->save();

                $quote = $this->cart->getQuote();
                $quoteId = $quote->getId();

                $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
                if (!$quoteIdMask->getId()) {
                    $quoteIdMask = $this->quoteIdMaskFactory->create();
                    $quoteIdMask->setQuoteId($quoteId);
                    $quoteIdMask->setDataChanges(true);
                    $quoteIdMask->save();
                }
                $maskedQuoteId =  $quoteIdMask->getMaskedId();

                if ($maskedQuoteId) {
                    $cookieDomain = $this->accountHelper->getCookieRedirect();
                    $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
                    $publicCookieMetadata->setPath('/');
                    $publicCookieMetadata->setDomain($cookieDomain);
                    $publicCookieMetadata->setHttpOnly(false);
                    $publicCookieMetadata->setSecure(true);
                    $publicCookieMetadata->setSameSite('Lax');
                    try {
                        $this->setCookie('abt_cartKey', $maskedQuoteId, $publicCookieMetadata);
                    } catch (\Exception $e) {
                        $this->logger->critical("Cart Creation Exception : ".$e->getMessage());
                    }
                }
                $quote->setIsPedialyteCart(true);
                $quote->save();
                $this->messageManager->addSuccessMessage(__('Product added to cart.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Product not found.'));
                $this->logger->info(__('Product not found.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Unable to add product to cart : '.$e->getMessage()));
                $this->logger->critical("Unable to add product to cart : ".$e->getMessage());
            }
            if ($queryString) {
                $queryString = rtrim($queryString, '/');
                return $resultRedirect->setUrl(self::CHECKOUT_CART_INDEX_REDIRECT . '?'
                    .$queryString);
            } else {
                return $this->_redirect(self::CHECKOUT_CART_INDEX_REDIRECT);
            }
        }
    }
}
