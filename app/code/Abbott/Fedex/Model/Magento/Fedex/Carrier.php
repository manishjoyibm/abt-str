<?php

namespace Abbott\Fedex\Model\Magento\Fedex;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Framework\Xml\Security;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Url\DecoderInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Carrier extends \Magento\Fedex\Model\Carrier
{
    /**
     * @var \Abbott\ShippingRestriction\Helper\Data
     */
    protected \Abbott\ShippingRestriction\Helper\Data $shippRestrictionHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cart;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected \Magento\Backend\Model\Session\Quote $adminQuote;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @var \Abbott\Fedex\Helper\Data
     */
    protected \Abbott\Fedex\Helper\Data $helper;

    private const SHIP_FEDEX_GROUND = "fedex_ground";
    private const SHIP_SMART_POST = "smart_post";
    private const XML_SMART_POST_SKU_PATH = 'hartehanks/hh_smartpost_skus/smart_post_sku';
    /**
     * @var bool
     */
    protected bool $isEnsureSample = false;
    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var Json
     */
    protected Json $serializer;

    /**
     * @var ClientFactory
     */
    private mixed $soapClientFactory;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param ElementFactory $xmlElFactory
     * @param ResultFactory $rateFactory
     * @param Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param ErrorFactory $trackErrorFactory
     * @param StatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param Data $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param Reader $configReader
     * @param CollectionFactory $productCollectionFactory
     * @param CurlFactory $curlFactory
     * @param DecoderInterface $decoderInterface
     * @param \Abbott\ShippingRestriction\Helper\Data $shippRestrictionHelper
     * @param ProductRepositoryInterface $productRepository
     * @param CartRepositoryInterface $cart
     * @param Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $adminQuote
     * @param CacheInterface $cache
     * @param \Abbott\Fedex\Helper\Data $helper
     * @param Json $serializer
     * @param array $data
     * @param ClientFactory $soapClientFactory
     */
    public function __construct(
        ScopeConfigInterface                                        $scopeConfig,
        Quote\Address\RateResult\ErrorFactory                       $rateErrorFactory,
        LoggerInterface                                             $logger,
        Security                                                    $xmlSecurity,
        ElementFactory                                              $xmlElFactory,
        ResultFactory                                               $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory              $trackFactory,
        ErrorFactory                                                $trackErrorFactory,
        StatusFactory                                               $trackStatusFactory,
        RegionFactory                                               $regionFactory,
        CountryFactory                                              $countryFactory,
        CurrencyFactory $currencyFactory,
        Data $directoryData,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        Reader $configReader,
        CollectionFactory $productCollectionFactory,
        CurlFactory $curlFactory,
        DecoderInterface $decoderInterface,
        \Abbott\ShippingRestriction\Helper\Data $shippRestrictionHelper,
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $cart,
        Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $adminQuote,
        CacheInterface $cache,
        \Abbott\Fedex\Helper\Data $helper,
        Json $serializer,
        EncryptorInterface $encryptor,
        ClientFactory $soapClientFactory,
        array $data = []
    ) {
        $this->shippRestrictionHelper = $shippRestrictionHelper;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->adminQuote = $adminQuote;
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
        $this->cache = $cache;
        $this->helper = $helper;
        $this->soapClientFactory = $soapClientFactory;
        $this->curlFactory = $curlFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $storeManager,
            $productCollectionFactory,
            $curlFactory,
            $decoderInterface,
            $data
        );
    }

    /**
     * Collect Rates
     *
     * @param RateRequest $request
     * @return bool|Quote\Address\RateResult\Error|Result|null
     */
    public function collectRates(RateRequest $request)
    {
        $isEnabled = $this->shippRestrictionHelper->isEnabled();
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $items = $request->getAllItems();
        if ($this->checkPoBoxValidation($request->getDestStreet())) {
            return false;
        }

        if ($isEnabled && !$this->checkShippingAvailability($items, $request->getDestRegionId())) {
            return false;
        }

        $this->setRequest($request);
        $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);
        return $this->getResult();
    }

    /**
     * Update FreeMethod Quote Method
     *
     * @param $request
     * @return void
     */
    protected function _updateFreeMethodQuote($request)
    {
        if (!$request->getFreeShipping()) {
            return;
        }
        if ($request->getFreeMethodWeight() == $request->getPackageWeight() || !$request->hasFreeMethodWeight()) {
            return;
        }
        $freeMethod = $this->getConfigData($this->_freeMethod);
        if (!$freeMethod) {
            return;
        }
        $freeRateId = false;
        if (is_object($this->_result)) {
            foreach ($this->_result->getAllRates() as $i => $item) {
                if ($item->getMethod()) {
                    $freeRateId = $i;
                    break;
                }
            }
        }
        if ($freeRateId === false) {
            return;
        }
        $price = null;

        if ($request->getFreeMethodWeight() > 0) {
            $this->_setFreeMethodRequest($freeMethod);
            $result = $this->_getQuotes();
            if ($result && ($rates = $result->getAllRates()) && !empty($rates)) {
                if (count($rates) == 1 && $rates[0] instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
                    $price = $rates[0]->getPrice();
                }
                if (count($rates) > 1) {
                    foreach ($rates as $rate) {
                        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method &&
                            $rate->getMethod() == $freeMethod

                        ) {
                            $price = $rate->getPrice();
                        }
                    }
                }
            }
        } else {
            /**
             * if we can apply free shipping for all order we should force price

             * to $0.00 for shipping without sending second request to carrier

             */
            $price = 0;
        }
        /**

         * if we did not get our free shipping method in response we must use its old price

         */

        if ($price !== null) {
            $this->_result->getRateById($freeRateId)->setPrice($price);
        }
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws LocalizedException
     */
    protected function _prepareRateResponse($response): Result
    {
        $costArr = [];
        $priceArr = [];
        $errorTitle = __('For some reason we can\'t retrieve tracking info right now.');
        $fedExFree = $this->shippRestrictionHelper->getFedExFreeShipping();
        $thresholdAmount = $this->shippRestrictionHelper->getFedExFreeThresholdAmount();
        $fedexSmartPostFreeEnabled = $this->helper->getSmartPostFreeShippingEnabled();
        $fedexSmartPostThresholdAmount = $this->helper->getSmartPostFreeShippingSubtotal();
        $flatGroundShipRate = $this->shippRestrictionHelper->getFlatRateGroundShipRate();
        $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

        if (is_array($response)) {
            if (!empty($response['errors'])) {
                if (is_array($response['errors'])) {
                    $notification = reset($response['errors']);
                    $errorTitle = (string)$notification['message'];
                    $apiStatus = $response['status_code'];
                    //Fallback rate visible when FedeEx goes down
                    if (($apiStatus == '500') || ($apiStatus == '503')) {
                        list($priceArr, $costArr) = $this->generateFallbackRates();
                    }
                } else {
                    $errorTitle = (string)$response['errors']['message'];
                }
            } elseif (isset($response['output']['rateReplyDetails'])) {
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
                if (is_array($response['output']['rateReplyDetails'])) {
                    foreach ($response['output']['rateReplyDetails'] as $rate) {
                        $serviceName = (string)$rate['serviceType'];
                        if (in_array($serviceName, $allowedMethods)) {
                            $amount = $this->_getRateAmountOriginBased($rate);
                            $costArr[$serviceName] = $amount;
                            $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                        }
                    }
                    asort($priceArr);
                }
            }
        } elseif (is_object($response)) {
            if ($response->HighestSeverity == 'ERROR') {
                if (is_array($response->Notifications)) {
                    $notification = array_pop($response->Notifications);
                    $errorTitle = (string) $notification->Message;
                } else {
                    $errorTitle = (string) $response->Notifications->Message;
                }
            } elseif ($response->HighestSeverity == 'FAILURE') {
                // in case if FedEx fails, return fallback rates.
                list($priceArr, $costArr) = $this->generateFallbackRates();
            } elseif (isset($response->RateReplyDetails)) {
                if (is_array($response->RateReplyDetails)) {
                    foreach ($response->RateReplyDetails as $rate) {
                        $serviceName = (string) $rate->ServiceType;
                        if (in_array($serviceName, $allowedMethods)) {
                            $amount = $this->_getRateAmountOriginBased($rate);
                            $costArr[$serviceName] = $amount;
                            $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                        }
                    }
                    asort($priceArr);
                } else {
                    $rate = $response->RateReplyDetails;
                    $serviceName = (string) $rate->ServiceType;
                    if (in_array($serviceName, $allowedMethods)) {
                        $amount = $this->_getRateAmountOriginBased($rate);
                        $costArr[$serviceName] = $amount;
                        $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                    }
                }
            }
        } else {
            // In case if prepareRateResponse gets an empty response ( which would happen for example in case of timeout
            // from origin. We will trigger a fallback mechanism and get rates from our flat table. This table features
            // 3 columns: method_name, subtotal, rate. Shipping is calculated based on subtotal greater than subtotal
            // from our flat table.
            list($priceArr, $costArr) = $this->generateFallbackRates();
        }
        $result = $this->_rateFactory->create();
        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorTitle);
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $shipMethod = strtolower($method);
                if ($this->isEnsureSample && $shipMethod == self::SHIP_FEDEX_GROUND) {
                    continue;
                }
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($this->getCode('method', $method));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                if ($fedExFree && ($fedExFree == $shipMethod)) {
                    $rate->setPrice(0);
                }
                if ($this->_storeManager->getStore()->getId() == 1 ||
                    $this->_storeManager->getStore()->getCode() ==
                    AccountHelper::NEW_SIM_STORE_CODE
                ) {
                    try {
                        $quoteId = $this->checkoutSession->getQuoteId();
                        $quote = $this->cart->get($quoteId);
                        /** @var Quote $subTotal */
                        $subTotal = $quote->getSubtotal();
                        $isAbbottSubscriptionItem = $quote->getExtensionAttributes()->getIsAbbottSubscriptionItem();
                        if ($flatGroundShipRate > 0 &&
                            $subTotal < $thresholdAmount &&
                            $shipMethod == self::SHIP_FEDEX_GROUND
                        ) {
                            $rate->setPrice($flatGroundShipRate);
                        }
                        if ($isAbbottSubscriptionItem && $shipMethod == self::SHIP_FEDEX_GROUND) {
                            $rate->setPrice(0.00);
                        }
                        if (($subTotal > $fedexSmartPostThresholdAmount ||
                                $isAbbottSubscriptionItem) &&
                            $shipMethod == self::SHIP_SMART_POST &&
                            $fedexSmartPostFreeEnabled
                        ) {
                            $rate->setPrice(0);
                        }
                        if ($this->isEnsureSample &&
                            $shipMethod == self::SHIP_SMART_POST &&
                            $subTotal < $fedexSmartPostThresholdAmount
                        ) {
                            $rate->setPrice($flatGroundShipRate);
                        }
                    } catch (\Exception $e) {
                        $this->_logger->critical($e);
                    }
                }
                if ($this->_storeManager->getStore()->getId() == AccountHelper::GLU_STORE_ID) {
                    try {
                        $quoteId = $this->checkoutSession->getQuoteId();
                        $quote = $this->cart->get($quoteId);
                        /** @var Quote $subTotal */
                        $subTotal = $quote->getSubtotal();

                        if ($shipMethod == self::SHIP_SMART_POST) {
                            $rate->setPrice(0.00);
                        }

                        if (($subTotal > $fedexSmartPostThresholdAmount) &&
                            $shipMethod == self::SHIP_SMART_POST &&
                            $fedexSmartPostFreeEnabled
                        ) {
                            $rate->setPrice(0);
                        }
                    } catch (\Exception $e) {
                        $this->_logger->critical($e);
                    }
                }
                if ($this->adminQuote->getQuote()->getTotals()['subtotal']->getValue()) {
                    $adminSubTotal = $this->adminQuote->getQuote()->getTotals()['subtotal']->getValue();
                    if ($adminSubTotal > $thresholdAmount && $shipMethod == self::SHIP_FEDEX_GROUND) {
                        $rate->setPrice(0.00);
                    } elseif ($adminSubTotal < $thresholdAmount && $shipMethod == self::SHIP_FEDEX_GROUND) {
                        $rate->setPrice($flatGroundShipRate);
                    } elseif ($adminSubTotal > $fedexSmartPostThresholdAmount &&
                        $shipMethod == self::SHIP_SMART_POST &&
                        $fedexSmartPostFreeEnabled) {
                        $rate->setPrice(0);
                    } elseif ($this->isEnsureSample && $shipMethod == self::SHIP_SMART_POST
                        && $adminSubTotal < $fedexSmartPostThresholdAmount) {
                        $rate->setPrice($flatGroundShipRate);
                    }
                }
                $result->append($rate);
            }
        }

        return $result;
    }

    /**
     * Check Shipping Availability
     *
     * @param Quote|array $items
     * @param $regionId
     * @return boolean
     */
    public function checkShippingAvailability(Quote|array $items, $regionId = null): bool
    {
        $productSku = null;
        foreach ($items as $item) {
            $productSku = $item->getSku();
            $productData = $this->shippRestrictionHelper->loadProductBySKU($productSku);
            if ($productData && $regionId && $productData->getData('abbott_shipping_restriction')) {
                $explodeStates = explode(",", $productData->getData('abbott_shipping_restriction'));
                if (in_array($regionId, $explodeStates)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check PoBox Validation
     *
     * @param $street
     * @return bool
     */
    public function checkPoBoxValidation($street = null): bool
    {
        if ($street && $this->shippRestrictionHelper->validateStreet($street)) {
            return true;
        }
        return false;
    }

    /**
     * Prepare and set request to this instance
     *
     * @param RateRequest $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setRequest(RateRequest $request)
    {
        $this->_request = $request;
        $r = new \Magento\Framework\DataObject();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        }

        if ($request->getFedexAccount()) {
            $account = $request->getFedexAccount();
        } else {
            $account = $this->getConfigData('account');
        }
        $r->setAccount($account);

        if ($request->getFedexDropoff()) {
            $dropoff = $request->getFedexDropoff();
        } else {
            $dropoff = $this->getConfigData('dropoff');
        }
        $r->setDropoffType($dropoff);

        if ($request->getFedexPackaging()) {
            $packaging = $request->getFedexPackaging();
        } else {
            $packaging = $this->getConfigData('packaging');
        }
        $r->setPackaging($packaging);

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        $r->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $r->setDestCountry($this->_countryFactory->create()->load($destCountry)->getData('iso2_code'));

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        if ($request->getDestCity()) {
            $r->setDestCity($request->getDestCity());
        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());

        $r->setWeight($weight);
        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setValue($request->getPackagePhysicalValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        $r->setPackages($this->createPackages((float) $request->getPackageWeight(), (array) $request->getPackages()));

        $r->setMeterNumber($this->getConfigData('meter_number'));
        $r->setKey($this->getConfigData('key'));
        $r->setPassword($this->getConfigData('password'));

        $r->setIsReturn($request->getIsReturn());

        $r->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->setRawRequest($r);

        return $this;
    }

    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     *
     * Used to reduce number of same requests done to carrier service during one session
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    protected function _getCachedQuotes($requestParams)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        if ($cache = $this->cache->load($key)) {
            return unserialize($cache);
        } else {
            return null;
        }
    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return $this
     */
    protected function _setCachedQuotes($requestParams, $response)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        $this->cache->save(serialize($response), $key, [\Abbott\Fedex\Model\Cache\ShippingCache::CACHE_TAG], 3600);

        return $this;
    }

    /**
     * Returns cache key for some request to carrier quotes service
     *
     * @param string|array $requestParams
     * @return string
     */
    protected function _getQuotesCacheKey($requestParams)
    {
        $key = parent::_getQuotesCacheKey($requestParams);
        return \Abbott\Fedex\Model\Cache\ShippingCache::CACHE_TAG . $key;
    }

    /**
     * Do remote request for and handle errors
     *
     * @return Result
     */
    protected function _getQuotes()
    {
        $this->_result = $this->_rateFactory->create();
        // make separate request for Smart Post method
        $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));

        // SOAP doesn't know how to handle socket time out any other way besides setting the global ini value
        // We have to capture previous default_socket_timeout values before transaction,
        // change it to our value and revert
        // back to previous value after transaction is complete in order to avoid this value impacting other services.
        $defaultSocketTimeout = null;
        $timeout = $this->helper->getFedexFallbackTimeout();
        if (!is_null($timeout) && is_numeric($timeout)) {
            $defaultSocketTimeout = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', $timeout);
        }
        if (in_array(self::RATE_REQUEST_SMARTPOST, $allowedMethods) &&
            in_array($this->_request->getDestRegionId(), $this->helper->getSmartPostStates())
        ) {
            $response = $this->_doRatesRequest(self::RATE_REQUEST_SMARTPOST);
            $preparedSmartpost = $this->_prepareRateResponse($response);
            if (!$preparedSmartpost->getError()) {
                $this->_result->append($preparedSmartpost);
            }
        } else {
            // make general request for all methods
            #ANAPOLLO-2935 Make SmartPost call, If Quote has EnsureSample SKUs
            if ($this->checkEnsureSampleSkus()) {
                $this->isEnsureSample = true;
                $smartPostResponse = $this->_doRatesRequest(self::RATE_REQUEST_SMARTPOST);
                $preparedSmartpost = $this->_prepareRateResponse($smartPostResponse);
                if (!$preparedSmartpost->getError()) {
                    $this->_result->append($preparedSmartpost);
                }
            }
            $response = $this->_doRatesRequest(self::RATE_REQUEST_GENERAL);
            $preparedGeneral = $this->_prepareRateResponse($response);
            if (!$preparedGeneral->getError()
                || $this->_result->getError() && $preparedGeneral->getError()
                || empty($this->_result->getAllRates())
            ) {
                $this->_result->append($preparedGeneral);
            }
        }
        if ($defaultSocketTimeout) {
            // Reverting back the ini value to what it was before this transaction
            ini_set('default_socket_timeout', $defaultSocketTimeout);
        }

        return $this->_result;
    }

    /**
     * Create soap client with selected wsdl
     *
     * @param string $wsdl
     * @param bool|int $trace
     * @return \SoapClient
     */
    protected function _createSoapClient($wsdl, $trace = false)
    {
        $timeout = $this->helper->getFedexFallbackTimeout();
        if ($wsdl == $this->_rateServiceWsdl && !is_null($timeout) && is_numeric($timeout)) {
            $params = ['trace' => $trace, "connection_timeout" => $timeout];
        } else {
            $params = ['trace' => $trace];
        }
        $client = $this->soapClientFactory->create($wsdl, $params);
        $client->__setLocation(
            $this->getConfigFlag(
                'sandbox_mode'
            ) ? $this->getConfigData('sandbox_webservices_url') : $this->getConfigData('production_webservices_url')
        );

        return $client;
    }

    /**
     * Send Curl Request
     *
     * @param string $endpoint
     * @param string $request
     * @param string|null $accessToken
     * @return array|bool
     * @throws LocalizedException
     */
    protected function sendRequest($endpoint, $request, $accessToken = null): array|bool
    {
        $timeout = $this->helper->getFedexFallbackTimeout();
        if ($accessToken) {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$accessToken,
                'X-locale' => 'en_US',
            ];
        } else {
            $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        }

        $curlClient = $this->curlFactory->create();
        $url = $this->_getUrl($endpoint);
        try {
            $curlClient->setHeaders($headers);
            if ($endpoint == self::SHIPMENT_CANCEL_END_POINT) {
                $curlClient->setOptions(
                    [
                        CURLOPT_ENCODING => 'gzip,deflate,sdch',
                        CURLOPT_CUSTOMREQUEST => 'PUT',
                        CURLOPT_CONNECTTIMEOUT => 0
                    ]
                );
            } elseif ($timeout == 0) {
                $curlClient->setOptions(
                    [
                        CURLOPT_ENCODING => 'gzip,deflate,sdch',
                        CURLOPT_CONNECTTIMEOUT => 1,
                        CURLOPT_TIMEOUT => 1
                    ]
                );
            } else {
                $curlClient->setOptions([CURLOPT_ENCODING => 'gzip,deflate,sdch',CURLOPT_CONNECTTIMEOUT => 0]);
            }
            $curlClient->post($url, $request);
            $response = $curlClient->getBody();
            $debugData = ['curl_response' => $response];
            $debugData['url'] = $url;
            $this->_debug($debugData);

            $responseWithStatus = $this->serializer->unserialize($response);
            $responseWithStatus['status_code'] = $curlClient->getStatus();
            $responseWithStatus = $this->serializer->serialize($responseWithStatus);

            return $this->serializer->unserialize($responseWithStatus);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return false;
    }

    /**
     * Send request for tracking
     *
     * @param string $tracking
     * @return void
     */
    protected function _getTrackingInformation($tracking): void
    {
        $accessToken = $this->_getTrackAPIAccessToken();

        if (!empty($accessToken)) {

            $trackRequest = [
                'includeDetailedScans' => true,
                'trackingInfo' => [
                    [
                        'trackingNumberInfo' => [
                            'trackingNumber'=> $tracking
                        ]
                    ]
                ]
            ];

            $requestString = $this->serializer->serialize($trackRequest);
            $response = $this->_getCachedQuotes($requestString);
            $debugData = ['request' => $trackRequest];

            if ($response === null) {
                $response = $this->sendRequest(self::TRACK_REQUEST_END_POINT, $requestString, $accessToken);
                $this->_setCachedQuotes($requestString, $response);
            }
            $debugData['result'] = $response;

            $this->_debug($debugData);
            $this->_parseTrackingResponse($tracking, $response);
        } else {
            $this->appendTrackingError(
                $tracking,
                __('Authorization Error. No Access Token found with given credentials.')
            );
            return;
        }
    }

    /**
     * Get Access Token for Rest Tracking API
     *
     * @return string|null
     */
    private function _getTrackAPIAccessToken(): string|null
    {
        $apiKey = $this->encryptor->decrypt($this->getConfigData('api_key_tracking')) ?? null;
        $secretKey = $this->encryptor->decrypt($this->getConfigData('secret_key_tracking')) ?? null;

        if (!$apiKey || !$secretKey) {
            $this->_debug(__('Authentication keys are missing.'));
            return null;
        }

        $requestArray = [
            'grant_type' => self::AUTHENTICATION_GRANT_TYPE,
            'client_id' => $apiKey,
            'client_secret' => $secretKey
        ];

        $request = http_build_query($requestArray);
        $accessToken = null;
        $response = $this->sendRequest(self::OAUTH_REQUEST_END_POINT, $request);

        if (!empty($response['errors'])) {
            $debugData = ['request_type' => 'Access Token Request', 'result' => $response];
            $this->_debug($debugData);
        } elseif (!empty($response['access_token'])) {
            $accessToken = $response['access_token'];
        }
        return $accessToken;
    }

    /**
     * Check Ensure Sample SKUs in Quote
     *
     * @return boolean
     */
    protected function checkEnsureSampleSkus()
    {
        $smartPostSku = $this->_scopeConfig->getValue(
            self::XML_SMART_POST_SKU_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $smartPostSkus = explode(",", $smartPostSku);
        $itemSkus = [];
        try {
            if ($quoteId = $this->checkoutSession->getQuoteId()) {
                $quote = $this->cart->get($quoteId);
                $quoteItems = $quote->getAllVisibleItems();
                $totalItems = $quote->getItemsCount();
                foreach ($quoteItems as $item) {
                    if (in_array($item->getSku(), $smartPostSkus)) {
                        $itemSkus[] = $item->getSku();
                    }
                }
            } else {
                $quoteItems = $this->adminQuote->getQuote()->getAllVisibleItems();
                $totalItems = $this->adminQuote->getQuote()->getItemsCount();
                foreach ($quoteItems as $item) {
                    if (in_array($item->getSku(), $smartPostSkus)) {
                        $itemSkus[] = $item->getSku();
                    }
                }
            }
            if (!empty($itemSkus) && count($itemSkus) == $totalItems) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate Fallback Rates
     *
     * @return array[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function generateFallbackRates()
    {
        $costArr = [];
        $priceArr = [];
        $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));
        $quote = null;
        try {
            if ($quoteId = $this->checkoutSession->getQuoteId()) {
                $quote = $this->cart->get($quoteId);
            } else {
                $quote = $this->adminQuote->getQuote();
                $quoteId = $quote->getId();
            }
            $subTotal = $quote->getSubtotal();
            // Generate Fedex timeout report and send over to New Relic
            $this->helper->generateTimeoutReport($quoteId);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        $rates = $this->helper->getFedexFallbackRates();
        $isSmartPost = false;
        // In case if this is smart post address, we will not display ground shipping rates
        if (in_array($this->_request->getDestRegionId(), $this->helper->getSmartPostStates())) {
            $isSmartPost = true;
        }
        if ($quote) {
            $rateSets = [];
            foreach ($rates as $rate) {
                if (in_array($rate["shipping_method"], $allowedMethods)) {
                    $method = $rate["shipping_method"];
                    // Show either smart post or regular rates based on isSmartPost flag
                    if ($isSmartPost == (strtolower($method) == self::SHIP_SMART_POST)) {
                        if (!isset($rateSets[$method])) {
                            $rateSets[$method] = [];
                        }
                        $rateSets[$method][] = $rate;
                    }
                }
            }

            foreach ($rateSets as $method => $rateSet) {
                $closestTotal = 0;
                foreach ($rateSet as $rate) {
                    if ($rate["subtotal"] <= $subTotal && $rate["subtotal"] >= $closestTotal) {
                        $priceArr[$method] = $rate['rate'];
                        $costArr[$method] = 0;
                        $closestTotal = $rate["subtotal"];
                    }
                }
            }
        }
        return [$priceArr, $costArr];
    }

    /**
     * Creates packages for rate request.
     *
     * @param float $totalWeight
     * @param array $packages
     * @return array
     */
    private function createPackages(float $totalWeight, array $packages): array
    {
        if (empty($packages)) {
            $dividedWeight = $this->getTotalNumOfBoxes($totalWeight);
            for ($i = 0; $i < $this->_numBoxes; $i++) {
                $packages[$i]['weight'] = $dividedWeight;
            }
        }
        $this->_numBoxes = count($packages);

        return $packages;
    }
}
