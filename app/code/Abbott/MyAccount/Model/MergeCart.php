<?php

declare(strict_types=1);

namespace Abbott\MyAccount\Model;

use Abbott\MyAccount\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class MergeCart
{
    public $cartManagement;
    public $maskedQuoteIdToQuoteId;
    public $cartRepository;
    public $checkoutSession;
    public $cookieManager;
    public $cookieMetadataFactory;
    public $accountHelper;
    public $storeManager;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Json
     */
    protected $jsonEncoder;

    /**
     * MergeCart constructor
     *
     * @param CartManagementInterface $cartManagement
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param Session $checkoutSession
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $cookieManager
     * @param Data $accountHelper
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param UrlInterface $urlBuilder
     * @param Json $jsonEncoder
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        Session $checkoutSession,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager,
        Data $accountHelper,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        UrlInterface $urlBuilder,
        Json $jsonEncoder
    ) {
        $this->cartManagement = $cartManagement;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->accountHelper = $accountHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * MergeCart function
     *
     * @param $customer
     * @param $guestCartKey
     * @param $customerCartKey
     * @param $merged
     * @return void
     */
    public function mergeCarts($customer, $guestCartKey, $customerCartKey, $merged)
    {
        if (($guestCartKey && $customerCartKey) && ($guestCartKey != $customerCartKey) && !$merged) {
            try {
                $storeId = $this->storeManager->getStore()->getId();
                $guestCartId = $this->maskedQuoteIdToQuoteId->execute($guestCartKey);
                $guestCart = $this->cartRepository->get($guestCartId);

                $customerCart =  $this->cartManagement->getCartForCustomer($customer->getId());
                if (($guestCart->getCustomerId() == 0 || $guestCart->getCustomerId() ==
                        $customer->getId()) && $guestCart->getStoreId() == $storeId &&
                    $guestCart->getIsActive()) {
                    $customerCart->merge($guestCart);
                    $guestCart->removeAllItems()->save();
                    $guestCart->setIsActive(false);
                    $this->processDuplicatesAfterMerge($customerCart);
                    $this->cartRepository->save($customerCart->collectTotals());
                    $this->cartRepository->save($guestCart);
                }
                $this->checkoutSession->replaceQuote($customerCart);
                $this->setCartKeyCookie($customerCartKey);
            } catch (NoSuchEntityException $e) {
                return;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                return;
            }
        }
    }

    /**
     * SetCartKeyCookie function
     *
     * @param $customerCartKey
     * @return void
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    protected function setCartKeyCookie($customerCartKey)
    {
        $publicCookieMetadata = $this->createPublicCookieMetaData();
        $this->cookieManager->deleteCookie('abt_sesCartKey', $publicCookieMetadata);
        $this->accountHelper->setCookie('abt_cartKey', $customerCartKey, $publicCookieMetadata);
    }

    /**
     * ProcessDuplicatesAfterMerge function
     *     * @param $customerCart
     * @return void
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function processDuplicatesAfterMerge($customerCart)
    {
        $customerCart->collectTotals();
        $cartItems = $customerCart->getAllItems();
        $items = [];
        $items = $this->getItems($cartItems, $items);
        $cartUpdated = false;
        foreach ($items as $itemsBySku) {
            if (count($itemsBySku) > 1) {
                $newestProduct = null;
                /** @var \Magento\Quote\Model\Quote\Item $itemBySku */
                foreach ($itemsBySku as $itemBySku) {
                    if (!$newestProduct) {
                        $newestProduct = $itemBySku;
                        continue;
                    }
                    if ($itemBySku->getCreatedAt() > $newestProduct->getCreatedAt()) {
                        $customerCart->deleteItem($newestProduct);
                        $newestProduct = $itemBySku;
                    } else {
                        $customerCart->deleteItem($itemBySku);
                    }
                }
                $cartUpdated = true;
            }
        }
        $this->abtDialogMessage($cartUpdated);
    }

    /**
     * CreatePublicCookieMetaData function
     *
     * @return \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata
     * @throws NoSuchEntityException
     */
    protected function createPublicCookieMetaData()
    {
        $cookieDomain = $this->accountHelper->getCookieRedirect();
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setDomain($cookieDomain);
        $publicCookieMetadata->setHttpOnly(false);
        $publicCookieMetadata->setSecure(true);
        $publicCookieMetadata->setSameSite('Lax');
        return $publicCookieMetadata;
    }

    /**
     * AbtDialogMessage function
     *
     * @param bool $cartUpdated
     * @return void
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function abtDialogMessage(bool $cartUpdated): void
    {
        if ($cartUpdated) {
            $publicCookieMetadata = $this->createPublicCookieMetaData();
            $message = $this->jsonEncoder->serialize([
                "message" => __("Your shopping cart has been updated."),
                "buttons" => [
                    [
                        "link" => $this->urlBuilder->getUrl("checkout/cart", ['_secure' => true]),
                        "label" => __("View Cart")
                    ]
                ]
            ]);
            $this->accountHelper->setCookie('abt_dialogMessage', $message, $publicCookieMetadata);
        }
    }

    /**
     * GetItems function
     *
     * @param mixed $cartItems
     * @param array $items
     * @return array
     */
    public function getItems(mixed $cartItems, array $items): array
    {
        foreach ($cartItems as $cartItem) {
            if (!isset($items[$cartItem->getSku()])) {
                $items[$cartItem->getSku()] = [];
            }
            $items[$cartItem->getSku()][] = $cartItem;
        }
        return $items;
    }
}
