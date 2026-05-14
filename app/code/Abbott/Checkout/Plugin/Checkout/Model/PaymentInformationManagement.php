<?php

namespace Abbott\Checkout\Plugin\Checkout\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;

class PaymentInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository = null)
    {
        $this->cartRepository = $cartRepository
            ?? ObjectManager::getInstance()->get(CartRepositoryInterface::class);
    }
    public function aroundSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        callable $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($billingAddress) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->getActive($cartId);
            $customerId = $quote->getBillingAddress()
                ->getCustomerId();
            if (!$billingAddress->getCustomerId() && $customerId) {
                //It's necessary to verify the price rules with the customer data
                $billingAddress->setCustomerId($customerId);
            }
            $saveInAddressBook = $quote->getBillingAddress()->getSaveInAddressBook();
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $billingAddress->setSaveInAddressBook($saveInAddressBook);
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress && $shippingAddress->getShippingMethod()) {
                $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
                if ($shippingRate) {
                    $shippingAddress->setLimitCarrier($shippingRate->getCarrier());
                }
            }
        }
        return $proceed($cartId, $paymentMethod, $billingAddress);
    }
}
