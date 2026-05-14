<?php

namespace Abbott\KountCustomFields\Model\Ris\Inquiry;

use Abbott\KountCustomFields\Helper\Data;
use Exception;
use Kount\Kount360\Model\Config\Account;
use Kount\Kount360\Model\Logger;
use Kount\Kount360\Model\Ris\Base\Builder\Session;
use Kount\Kount360\Model\Ris\Inquiry\Builder\VersionInfo;
use Kount\Kount360\Model\Ris\InquiryFactory;
use Magento\Sales\Model\Order;
use Kount\Kount360\Model\RisService;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;

class Builder extends \Kount\Kount360\Model\Ris\Inquiry\Builder
{
    /**
     * @var Data
     */
    protected Data $helper;
    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PaymentInterface
     */
    protected $paymentBuilder;

    /**
     * Builder constructor.
     * @param InquiryFactory $inquiryFactory
     * @param Account $configAccount
     * @param VersionInfo $versionBuilder
     * @param Session $sessionBuilder
     * @param \Kount\Kount360\Model\Ris\Inquiry\Builder\Order $orderBuilder
     * @param Data $helper
     * @param Logger $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param PaymentInterface $PaymentInterface
     */
    public function __construct(
        InquiryFactory $inquiryFactory,
        Account $configAccount,
        VersionInfo $versionBuilder,
        Session $sessionBuilder,
        \Kount\Kount360\Model\Ris\Inquiry\Builder\Order $orderBuilder,
        Data $helper,
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        PaymentInterface $paymentBuilder
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->paymentBuilder = $paymentBuilder;
        parent::__construct(
            $inquiryFactory,
            $configAccount,
            $versionBuilder,
            $sessionBuilder,
            $orderBuilder,
        );
    }

    /**
     * @param Order $order
     * @param string $auth
     * @param string $mack
     * @return \Magento\Framework\DataObject
     */
    public function build(Order $order, $auth = RisService::AUTH_AUTHORIZED, $mack = RisService::MACK_YES)
    {
        $inquiry = $this->inquiryFactory->create($order->getStore()->getWebsiteId());

        try {
            $this->orderBuilder->process($inquiry, $order);
            $this->paymentBuilder->process($inquiry, $order->getPayment());

            $this->setChannel($inquiry, $this->helper->getChannel($order->getStore()->getWebsiteId()));

            $this->helper->setAbbottUserDefinedFields($inquiry, $order);
            
            if ($this->helper->isZeroDollarWebsiteEnabled($order->getStore()->getWebsiteId())
                && $order->getGrandTotal() <= 0
            ) {
                $this->setChannel($inquiry, $this->helper->getZeroDollarWebsite($order->getStore()->getWebsiteId()));
                $this->helper->setPromoDetails($inquiry, $order);
            }

            $isGuest = $order->getCustomerIsGuest();
            $storeCode = $order->getStore()->getCode();

            if ($isGuest && $storeCode == 'pedialyte') {
                // The order was placed by a guest
                $this->helper->setPDLGuestUDF($inquiry);
            }

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $inquiry;
    }

    /**
     * Set Channel
     *
     * @param \Magento\Framework\DataObject $inquiry
     * @param string $channel
     */
    protected function setChannel($inquiry, $channel)
    {
        $inquiry->setData('channel', $channel);
    }
}
