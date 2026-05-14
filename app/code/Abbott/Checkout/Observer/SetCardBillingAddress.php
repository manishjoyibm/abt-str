<?php

namespace Abbott\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use \Psr\Log\LoggerInterface;
use PayPal\Braintree\Model\Ui\ConfigProvider;

class SetCardBillingAddress implements ObserverInterface
{
    /**
     * @var \Magento\Vault\Model\ResourceModel\PaymentToken
     */
    public $tokendata;
    public $addressRepository;
    public $linkAddress;
    public $paymentToken;
    public $quote;
    protected $productRepository;

    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Vault\Model\ResourceModel\PaymentToken   $tokenData
     * @param \Magento\Customer\Api\AddressRepositoryInterface  $addressRepository
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Abbott\CreditCards\Model\AddressPaymentTokenLink $linkAddress
     * @param \Psr\Log\LoggerInterface                          $logger
     */
    public function __construct(
        \Magento\Vault\Model\ResourceModel\PaymentToken $tokenData,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Abbott\CreditCards\Model\AddressPaymentTokenLinkFactory $linkAddress,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentToken,
        \Magento\Quote\Model\QuoteFactory $quote
    ) {
        $this->tokendata = $tokenData;
        $this->addressRepository = $addressRepository;
        $this->linkAddress = $linkAddress;
        $this->logger = $logger;
        $this->paymentToken = $paymentToken;
        $this->quote = $quote;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $order = $observer->getEvent()->getOrder();

        try {
            if ($order->getPayment()->getMethod() == ConfigProvider::CC_VAULT_CODE) {
                $paymentData = $order->getPayment()->getAdditionalInformation();
                $tokenData = $this->paymentToken->getByPublicHash(
                    $paymentData['public_hash'],
                    $paymentData['customer_id']
                );
                $addId = $this->linkAddress->create()->getAddressIdByPaymentId($tokenData->getEntityId());
                $address = $this->addressRepository->getById($addId);
                $quote = $this->quote->create()->load($order->getQuoteId());
                $firstName = !empty($address->getFirstname()) ? $address->getFirstname() : '';
                $middleName = !empty($address->getMiddlename()) ? $address->getMiddlename() : '';
                $lastName = !empty($address->getLastname()) ? $address->getLastname() : '';
                $suffix = !empty($address->getSuffix()) ? $address->getSuffix() : '' ;
                $prefix = !empty($address->getPrefix()) ? $address->getPrefix() : '';
                $region = !empty($address->getRegion()->getRegion()) ? $address->getRegion()->getRegion() : '';
                $regionId = !empty($address->getRegionId()) ? $address->getRegionId() : '';
                $street = !empty($address->getStreet()) ? $address->getStreet() : '';
                $city = !empty($address->getCity()) ? $address->getCity() : '';
                $telephone = !empty($address->getTelephone()) ? $address->getTelephone() : '';
                $postCode = !empty($address->getPostcode()) ? $address->getPostcode() : '';
                $countryId = !empty($address->getCountryId()) ? $address->getCountryId() : '';

                $quote->getBillingAddress()->setFirstname($firstName);
                $quote->getBillingAddress()->setMiddlename($middleName);
                $quote->getBillingAddress()->setLastname($lastName);
                $quote->getBillingAddress()->setCustomerAddressId($address->getId());
                $quote->getBillingAddress()->setSuffix($suffix);
                $quote->getBillingAddress()->setPrefix($prefix);
                $quote->getBillingAddress()->setRegion($region);
                $quote->getBillingAddress()->setRegionId($regionId);
                $quote->getBillingAddress()->setStreet($street);
                $quote->getBillingAddress()->setCity($city);
                $quote->getBillingAddress()->setTelephone($telephone);
                $quote->getBillingAddress()->setPostcode($postCode);
                $quote->getBillingAddress()->setCountryId($countryId);

                $quote->getBillingAddress()->save();

                $order->getBillingAddress()->setFirstname($firstName);
                $order->getBillingAddress()->setMiddlename($middleName);
                $order->getBillingAddress()->setLastname($lastName);
                $order->getBillingAddress()->setSuffix($suffix);
                $order->getBillingAddress()->setPrefix($prefix);
                $order->getBillingAddress()->setRegion($region);
                $order->getBillingAddress()->setRegionId($regionId);
                $order->getBillingAddress()->setStreet($street);
                $order->getBillingAddress()->setCity($city);
                $order->getBillingAddress()->setTelephone($telephone);
                $order->getBillingAddress()->setPostcode($postCode);
                $order->getBillingAddress()->setCountryId($countryId);

            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
