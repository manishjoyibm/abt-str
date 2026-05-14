<?php

namespace Abbott\ShippingRestriction\Plugin;

use Abbott\ShippingRestriction\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\State;


class RestrictMethod
{

    public $checkoutSession;
    protected $shippRestrictionHelper;

    public const RESTRICT_SHIP_METHOD = ['FEDEX_GROUND'];
    public const SMART_POST = 'smart_post';
    public const ADMIN_AREA = 'adminhtml';

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    protected $storeManager;
    /**
     * @var Quote
     */
    protected $quoteSession;
    /**
     * @var State
     */
    protected $state;

    /**
     * Construct function
     *
     * @param Data $shippRestrictionHelper
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param Quote $quoteSession
     * @param State $state
     */
    public function __construct(
        Data $shippRestrictionHelper,
        Session $checkoutSession,
        CartRepositoryInterface $cartRepository,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Quote $quoteSession,
        State $state
    ) {
        $this->shippRestrictionHelper = $shippRestrictionHelper;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->quoteSession = $quoteSession;
        $this->state = $state;
    }

    /**
     * Before Append function
     *
     * @param $subject
     * @param $result
     * @return array|false
     */
    public function beforeAppend($subject, $result)
    {
        if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            return [$result];
        }
        try {
            if($this->getArea() == self::ADMIN_AREA) {
                $backendQuote = $this->quoteSession->getQuote();
                $quoteId = $backendQuote->getId();
            } else {
                $quoteId = $this->checkoutSession->getQuoteId();
            } 
            $quote = $this->cartRepository->get($quoteId);
            $isHazardous = $quote->getExtensionAttributes()->getIsItemHazard();
            if ($this->isMethodRestricted($result, $isHazardous)) {
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return [$result];
    }

    /**
     * IsMethodRestricted function
     *
     * @param $shippingModel
     * @param $isHazardous
     * @return bool
     */
    public function isMethodRestricted($shippingModel, $isHazardous = null)
    {
        $code = $shippingModel->getMethod();
        $fedExFreeMethod = $this->shippRestrictionHelper->getFedExFreeShipping();
        $shipMethod = strtolower($shippingModel->getMethod());
        if (!in_array($code, self::RESTRICT_SHIP_METHOD) && $isHazardous) {
            return true;
        }
        if ($fedExFreeMethod && ($fedExFreeMethod != $shipMethod) && ($shipMethod != self::SMART_POST)) {
            return true;
        }
        return false;
    }

    /**
     * Get Area function
     *
     * @return string|null
     */
    public function getArea()
    {
        return $this->state->getAreaCode();
    }
}
