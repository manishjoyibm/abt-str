<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Checkout;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 * Class ConfigProvider
 * @package Aheadworks\Sarp2\Model\Checkout
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param HasSubscriptions $quoteChecker
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        HasSubscriptions $quoteChecker,
        UrlInterface $urlBuilder
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->quoteChecker = $quoteChecker;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            try {
                /** @var CartInterface|Quote $quote */
                $quote = $this->quoteRepository->getActive($quoteId);
                $config['isAwSarp2QuoteSubscription'] = $this->quoteChecker->checkHasSubscriptionsOnly(
                    $quote
                );
                $config['isAwSarp2QuoteMixed'] = $this->quoteChecker->checkHasBoth($quote);
            } catch (NoSuchEntityException $e) {
            }
        }
        return $config;
    }
}
