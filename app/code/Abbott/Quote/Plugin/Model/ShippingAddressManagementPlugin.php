<?php


namespace Abbott\Quote\Plugin\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingAddressManagement;

class ShippingAddressManagementPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * ShippingAddressManagementPlugin constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Running before plugin to check if customer address belongs to user and clearing it if it does not
     * @param ShippingAddressManagement $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     */
    public function beforeAssign(
        ShippingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        try {
            if ($addressId = $address->getCustomerAddressId()) {
                $customerAddress = $this->addressRepository->getById($addressId);
                if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
                    $address->setCustomerAddressId(null);
                }
            }
        } catch (NoSuchEntityException $e) {
            $address->setCustomerAddressId(null);
        }

        return [$cartId, $address];
    }
}
