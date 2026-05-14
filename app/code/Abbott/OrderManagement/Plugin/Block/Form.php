<?php

namespace Abbott\OrderManagement\Plugin\Block;

use Magento\Backend\Model\Session\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PayPal\Braintree\Model\Adminhtml\Source\CcType;
use PayPal\Braintree\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Form
{
    /**
     * @var Quote
     */
    protected $sessionQuote;

    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CcType
     */
    protected $ccType;

    /**
     * @var Config
     */
    protected $gatewayConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Quote $sessionQuote,
        ScopeConfigInterface $scopeConfig,
        CcType $ccType,
        GatewayConfig $gatewayConfig,
        LoggerInterface $logger
    ) {
        $this->sessionQuote = $sessionQuote;
        $this->scopeConfig = $scopeConfig;
        $this->ccType = $ccType;
        $this->gatewayConfig = $gatewayConfig;
        $this->logger = $logger;
    }

    public function aroundGetCcAvailableTypes(\PayPal\Braintree\Block\Form $subject, callable $proceed)
    {
        try {
            $configuredCardTypes = $this->getConfiguredCardTypes();
            $countryId = $this->sessionQuote->getQuote()->getBillingAddress()->getCountryId() ??
                $this->getCountryByWebsite();
            return $this->filterCardTypesForCountry($configuredCardTypes, $countryId);
        } catch (InputException $e) {
            $this->logger->critical($e->getMessage());
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
        }

        return [];
    }

    private function getConfiguredCardTypes(): array
    {
        $types = $this->ccType->getAllowedTypes();
        $configCardTypes = array_fill_keys($this->gatewayConfig->getAvailableCardTypes(), '');

        return array_intersect_key($types, $configCardTypes);
    }

    private function filterCardTypesForCountry(array $configCardTypes, string $countryId): array
    {
        $filtered = $configCardTypes;
        $countryCardTypes = $this->gatewayConfig->getCountryAvailableCardTypes($countryId);

        // filter card types only if specific card types are set for country
        if (!empty($countryCardTypes)) {
            $availableTypes = array_fill_keys($countryCardTypes, '');
            $filtered = array_intersect_key($filtered, $availableTypes);
        }

        return $filtered;
    }
    /**
     * Get Country code by website scope
     *
     * @return string
     */
    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
}
