<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Info;

use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Initialization
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Info
 */
class Initialization
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        RemoteAddress $remoteAddress
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Initialize sampler info instance
     *
     * @param SamplerInfoInterface $info
     * @param PaymentInterface $payment
     * @return SamplerInfoInterface
     */
    public function init(SamplerInfoInterface $info, PaymentInterface $payment)
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $info->setStatus(SamplerInfoInterface::STATUS_PENDING)
            ->setMethod($payment->getMethod())
            ->setStoreId($store->getId())
            ->setCurrencyCode($store->getCurrentCurrencyCode())
            ->setCustomerId($this->customerSession->getCustomerId());
        $remoteIp = $this->remoteAddress->getRemoteAddress();
        if ($remoteIp) {
            $info->setRemoteIp($remoteIp);
        }
        return $info;
    }
}
