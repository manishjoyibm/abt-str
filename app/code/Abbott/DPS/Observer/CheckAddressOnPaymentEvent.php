<?php


namespace Abbott\DPS\Observer;

use Abbott\DPS\Api\Data\DpsListItemInterface;
use Abbott\DPS\Api\Data\DpsListLogInterface;
use Abbott\DPS\Helper\Data;
use Abbott\DPS\Model\DpsListItemAddress;
use Abbott\DPS\Model\DpsListLogFactory;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Abbott\DPS\Model\ResourceModel\DpsListItem\CollectionFactory as DpsListItemCollectionFactory;
use Psr\Log\LoggerInterface;

class CheckAddressOnPaymentEvent implements ObserverInterface
{
    /**
     * @var DpsListItemCollectionFactory
     */
    private DpsListItemCollectionFactory $collectionFactory;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var DpsListLogFactory
     */
    protected DpsListLogFactory $dpsListLogFactory;

    /**
     * CheckAddressOnPaymentEvent constructor.
     * @param DpsListItemCollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param DpsListLogFactory $dpsListLogFactory
     */
    public function __construct(
        DpsListItemCollectionFactory $collectionFactory,
        LoggerInterface $logger,
        Data $helper,
        DpsListLogFactory $dpsListLogFactory
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->dpsListLogFactory = $dpsListLogFactory;
    }

    /**
     * Method Execute
     *
     * @param Observer $observer
     * @return false|void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        try {
            if (!$this->helper->isEnabled()) {
                return false;
            }
            /** @var CartInterface $quote */
            $quote = $observer->getData('quote');
            $shippingAddress = $quote->getShippingAddress();
            $billingAddress = $quote->getBillingAddress();
            $collection = $this->collectionFactory->create();
            if ($collection->count() > 0) {
                /** @var DpsListItemInterface $dpsItem */
                foreach ($collection as $dpsItem) {
                    $this->matchAddress($shippingAddress, $dpsItem);
                    $this->matchAddress($billingAddress, $dpsItem);
                }
            }
        } catch (LocalizedException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Method matchAddress
     *
     * @param mixed $quoteAddress
     * @param mixed $dpsItem
     * @throws LocalizedException
     */
    protected function matchAddress(mixed $quoteAddress, mixed $dpsItem): void
    {
        $match = false;
        if ($quoteAddress->getCompany()) {
            if ($this->compare($quoteAddress->getCompany(), $dpsItem->getName()) > $this->helper->getNamePercentage()) {
                $match = true;
            }
        } else {
            if ($this->compare(
                $quoteAddress->getFirstname()." ".$quoteAddress->getLastname(),
                $dpsItem->getName()
            ) > $this->helper->getNamePercentage()
            ) {
                $match = true;
            } elseif ($this->compare(
                $quoteAddress->getLastname().", ".$quoteAddress->getFirstname(),
                $dpsItem->getName()
            ) > $this->helper->getNamePercentage()) {
                $match = true;
            }
        }
        if ($match && ($addresses = $dpsItem->getAddresses())) {
            /** @var DpsListItemAddress $address */
            foreach ($addresses as $address) {
                if ($this->compare(
                    strtolower($address->getAddress()),
                    strtolower(implode(",", $quoteAddress->getStreet()))
                ) > $this->helper->getStreetPercentage() &&
                    $this->compare(strtolower($address->getCity()), strtolower($quoteAddress->getCity())) >
                    $this->helper->getCityPercentage() && $this->compare(
                        $this->getPostalCode($address->getPostalCode()),
                        $this->getPostalCode($quoteAddress->getPostcode())
                    ) > $this->helper->getZipPercentage()
                ) {
                    /** @var DpsListLogInterface $dpsListLog */
                    $dpsListLog = $this->dpsListLogFactory->create();
                    $dpsListLog->setName($quoteAddress->getFirstName() . " " . $quoteAddress->getLastName());
                    $dpsListLog->setAddress(implode(",", $quoteAddress->getStreet()));
                    $dpsListLog->setCity($quoteAddress->getCity());
                    $dpsListLog->setState($quoteAddress->getRegionCode());
                    $dpsListLog->setPostalCode($quoteAddress->getPostcode());
                    $dpsListLog->setCountry($quoteAddress->getCountryId());
                    $dpsListLog->save();
                    throw new LocalizedException(__($this->helper->getErrorMessage()));
                }
            }
        }
    }

    /**
     * Method to get Postal Code
     *
     * @param string $postalCode
     * @return string
     */
    protected function getPostalCode(string $postalCode): string
    {
        if ($postalCode) {
            $postalCodeArr = explode("-", $postalCode);
        }
        return $postalCodeArr[0] ?? "";
    }

    /**
     * Method compare
     *
     * @param string $originalString
     * @param string $targetString
     * @return int|float
     */
    protected function compare(string $originalString, string $targetString): int|float
    {
        $percentageMatch = 0;
        similar_text(strtolower($originalString), strtolower($targetString), $percentageMatch);
        return $percentageMatch;
    }
}
