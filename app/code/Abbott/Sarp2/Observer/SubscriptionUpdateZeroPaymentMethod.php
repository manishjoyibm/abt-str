<?php

declare(strict_types=1);

namespace Abbott\Sarp2\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart;
use Aheadworks\Sarp2\Model\Checkout\ConfigProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;

class SubscriptionUpdateZeroPaymentMethod implements ObserverInterface

{
    protected $_appState;
    const GLUCERNA_STORE_ID = 2;
    const CHECKMO = 'checkmo';
    const PURCHASEORDER = 'purchaseorder';

    protected $cart;
    protected $configProvider;
    protected $storeManager;

    public function __construct(Cart $cart, ConfigProvider $configProvider, StoreManagerInterface $storeManager,State $appState)

    {
        $this->cart = $cart;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->_appState = $appState;
    }

    /**

     * @param Observer $observer

     * @return $this;

     * @throws Exception

     */

    public function execute(Observer $observer)

    {

        /** @var MethodInterface $methodInstance */
        $methodInstance = $observer->getEvent()->getMethodInstance();

        $quote = $observer->getEvent()->getQuote();
        $result = $observer->getEvent()->getResult();
        $storeId = $this->storeManager->getStore()->getId();

        if (!$quote) {
            return;
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        if (($quote->getBaseGrandTotal() == 0) && ($quote->getData('aw_sarp_regular_subtotal') > 0) && ($quote->getShippingAddress()->getBaseShippingAmount() == 0) && ($storeId != self::GLUCERNA_STORE_ID)) {
            $cartItems = $this->cart->getQuote()->getAllVisibleItems();
            foreach ($cartItems as $cartItem) {
                if ($cartItem->getOptionByCode('aw_sarp2_subscription_type')) {
                    $optionIds = $cartItem->getOptionByCode('aw_sarp2_subscription_type');
                    if ($optionIds) {
                        foreach (explode(',', $optionIds->getValue()) as $optionId) {
                            $cartItem->removeOption('option_' . $optionId);
                        }
                        $cartItem->removeOption('aw_sarp2_subscription_type');
                    }
                    $cartItem->saveItemOptions();
                }
            }
            $couponCode = $quote->getData('coupon_code');
            $this->cart->getQuote()->setCouponCode('')->collectTotals()->save();
            $quote->setCouponCode($couponCode)
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        }
        $result->setData('is_available', true);
        if ($methodInstance->getCode() == self::CHECKMO && $this->_appState->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $result->setData('is_available', false);
        }
        if ($methodInstance->getCode() == self::PURCHASEORDER && $this->_appState->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $result->setData('is_available', false);
        }
        return $this;
    }
}
