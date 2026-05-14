<?php
namespace Abbott\Quote\Model\QuoteRepository;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Model\Quote\Address\BillingAddressPersister;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister;
use Magento\Quote\Model\ResourceModel\Quote;

/**
 * Handler for saving quote.
 */
class SaveHandler
{
    /**
     * @var CartItemPersister
     */
    private CartItemPersister $cartItemPersister;

    /**
     * @var BillingAddressPersister
     */
    private BillingAddressPersister $billingAddressPersister;

    /**
     * @var Quote
     */
    private Quote $quoteResourceModel;

    /**
     * @var ShippingAssignmentPersister
     */
    private ShippingAssignmentPersister $shippingAssignmentPersister;

    /**
     * @var AddressRepositoryInterface
     */
    private mixed $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private mixed $quoteAddressFactory;

    /**
     * @param Quote $quoteResource
     * @param CartItemPersister $cartItemPersister
     * @param BillingAddressPersister $billingAddressPersister
     * @param ShippingAssignmentPersister $shippingAssignmentPersister
     * @param AddressRepositoryInterface|null $addressRepository
     * @param AddressInterfaceFactory|null $addressFactory
     */
    public function __construct(
        Quote $quoteResource,
        CartItemPersister $cartItemPersister,
        BillingAddressPersister $billingAddressPersister,
        ShippingAssignmentPersister $shippingAssignmentPersister,
        AddressRepositoryInterface $addressRepository = null,
        AddressInterfaceFactory $addressFactory = null
    ) {
        $this->quoteResourceModel = $quoteResource;
        $this->cartItemPersister = $cartItemPersister;
        $this->billingAddressPersister = $billingAddressPersister;
        $this->shippingAssignmentPersister = $shippingAssignmentPersister;
        $this->addressRepository = $addressRepository
            ?: ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
        $this->quoteAddressFactory = $addressFactory ?:ObjectManager::getInstance()
            ->get(AddressInterfaceFactory::class);
    }

    /**
     * Process and save quote data
     *
     * @param CartInterface $quote
     * @return CartInterface|\Magento\Quote\Model\Quote
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     */
    public function save(CartInterface $quote): CartInterface|\Magento\Quote\Model\Quote
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        // Quote Item processing
        $items = $quote->getItems();

        if ($items) {
            foreach ($items as $item) {
                /** @var Item $item */
                if (!$item->isDeleted()) {
                    $quote->setLastAddedItem($this->cartItemPersister->save($quote, $item));
                } elseif (count($items) === 1) {
                    $quote->setBillingAddress($this->quoteAddressFactory->create());
                    $quote->setShippingAddress($this->quoteAddressFactory->create());
                }
            }
        }

        // Billing Address processing
        $billingAddress = $quote->getBillingAddress();

        if ($billingAddress) {
            if ($billingAddress->getCustomerAddressId()) {
                try {
                    $address = $this->addressRepository->getById($billingAddress->getCustomerAddressId());
                    // AN_6533-2319
                    // Customer quote contains address id that doesn't belong to him, which causes an exception.
                    // We will check if address belongs to this customer to avoid such issue
                    if ($address->getCustomerId() != $quote->getCustomerId()) {
                        $billingAddress->setCustomerAddressId(null);
                    }
                } catch (NoSuchEntityException $e) {
                    $billingAddress->setCustomerAddressId(null);
                }
            }

            $this->billingAddressPersister->save($quote, $billingAddress);
        }

        $this->processShippingAssignment($quote);
        $this->quoteResourceModel->save($quote->collectTotals());

        return $quote;
    }

    /**
     * Process shipping assignment
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     * @throws InputException
     */
    private function processShippingAssignment($quote): void
    {
        // Shipping Assignments processing
        $extensionAttributes = $quote->getExtensionAttributes();

        if (!$quote->isVirtual() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
            $shippingAssignments = $extensionAttributes->getShippingAssignments();

            if (count($shippingAssignments) > 1) {
                throw new InputException(__('Only 1 shipping assignment can be set'));
            }

            $this->shippingAssignmentPersister->save($quote, $shippingAssignments[0]);
        }
    }
}
