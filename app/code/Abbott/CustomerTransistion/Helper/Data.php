<?php

namespace Abbott\CustomerTransistion\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    public const XML_PATH_MYACCOUNT_FAILURE_REDIRECT = 'my_account/myAccount_redirect/redirect_failure_url';

    public const XML_PATH_MYACCOUNT_AEM_JS_VERSION = 'my_account/myAccount_redirect/js_version';

    public const XML_PATH_MYACCOUNT_REDIRECT = 'my_account/redirect_settings/aem_no_order_page';

    public const XML_PATH_EMPTY_CART_REDIRECT = 'my_account/redirect_settings/empty_cart_url';

    public const XML_PATH_CONTINUE_SHOPPING_REDIRECT = 'my_account/redirect_settings/cart_continue_shopping_url';

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Construct function
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

   /**
    * Get FailureUrl
    *
    * @return mixed
    * @throws NoSuchEntityException
    */
    public function getFailureUrl(): mixed
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MYACCOUNT_FAILURE_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * GetStore ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get Shopping URL Similac
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getShoppingUrlSimilac(): string
    {
        $productpage = $this->scopeConfig->getValue(
            self::XML_PATH_EMPTY_CART_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return $this->getFailureUrl() .$productpage;
    }

    /**
     * Get AEM JS Version
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getAemJsVersion(): mixed
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MYACCOUNT_AEM_JS_VERSION,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Continue Shopping URL
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getContinueCartShoppingUrl(): mixed
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTINUE_SHOPPING_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

}
