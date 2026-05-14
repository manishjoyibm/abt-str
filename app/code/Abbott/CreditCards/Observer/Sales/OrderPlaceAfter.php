<?php

namespace Abbott\CreditCards\Observer\Sales;

use Abbott\CreditCards\Model\AddressPaymentTokenLink;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Vault\Model\ResourceModel\PaymentToken;
use Psr\Log\LoggerInterface;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var AddressPaymentTokenLink
     */
    protected AddressPaymentTokenLink $linkAddress;

    /**
     * @var PaymentToken
     */
    private PaymentToken $tokendata;

    /**
     * @var AddressRepositoryInterface
     */
    private AddressRepositoryInterface $addressRepository;

    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PaymentToken $tokenData
     * @param AddressRepositoryInterface $addressRepository
     * @param Session $customerSession
     * @param AddressPaymentTokenLink $linkAddress
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentToken $tokenData,
        AddressRepositoryInterface $addressRepository,
        Session $customerSession,
        AddressPaymentTokenLink $linkAddress,
        LoggerInterface $logger
    ) {
        $this->tokendata = $tokenData;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
        $this->linkAddress = $linkAddress;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ): void {
        $order = $observer->getEvent()->getOrder();
        try {
            $paymentId = $order->getPayment()->getId();
            $tokenrow = $paymentId ? $this->tokendata->getByOrderPaymentId($paymentId) : [];
            $tokenId = $tokenrow ? $tokenrow['entity_id'] : 0;
            $addId = $this->linkAddress->getAddressIdByPaymentId($tokenId);
            if ($this->compareBillingAndShippingAddress($order)) {
                $customerAddressId = $order->getShippingAddress()->getCustomerAddressId();
            } else {
                $customerAddressId = $order->getBillingAddress()->getCustomerAddressId();
            }
            if (!$customerAddressId && $this->customerSession->isLoggedIn()) {
                $customerAddressId = $this->customerSession->getCustomer()->getDefaultBilling();
            }
            if ($customerAddressId && $tokenId && !$addId) {
                $address = $this->addressRepository->getById($customerAddressId);
                $this->addressRepository->save($address);
                $this->linkAddress->addLinkToAddressPayment($tokenId, $address->getId());
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Returns true if shipping address is same as billing 
     *
     * @param Order $order
     * @return bool
     */
    public function compareBillingAndShippingAddress($order): bool
    {
        $isAddressDiff = true;
        $excludeKeys = ['entity_id',
        'customer_address_id', 'quote_address_id', 'region_id', 'customer_id', 'address_type'];
        $oBillingAddress = $order->getBillingAddress()->getData();
        $oShippingAddress = $order->getShippingAddress()->getData();
        $oBillingAddressFiltered = array_diff_key($oBillingAddress, array_flip($excludeKeys));
        $oShippingAddressFiltered = array_diff_key($oShippingAddress, array_flip($excludeKeys));
        $addressDiff = array_diff($oBillingAddressFiltered, $oShippingAddressFiltered);
        if ($addressDiff) {
            $isAddressDiff = false;

        }
        return $isAddressDiff;
    }
}
