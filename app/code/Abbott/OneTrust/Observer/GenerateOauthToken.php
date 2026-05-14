<?php

declare(strict_types=1);

namespace Abbott\OneTrust\Observer;

use Abbott\OneTrust\Helper\Data;
use Abbott\OneTrust\Logger\Logger;
use Abbott\OneTrust\Model\OneTrust;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class GenerateOauthToken implements ObserverInterface
{
    /**
     * @var OneTrust
     */
    protected $onetrust;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * GenerateOauthToken constructor.
     * @param OneTrust $oneTrust
     * @param Logger $logger
     * @param Data $helper
     */
    public function __construct(
        OneTrust $oneTrust,
        Logger $logger,
        Data $helper
    ) {
        $this->onetrust = $oneTrust;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
        try {
            if (!empty($observer->getWebsite())) {
                $scope = ScopeInterface::SCOPE_WEBSITE;
                $scopeId = $observer->getWebsite();
            } elseif (!empty($observer->getStore())) {
                $scope = ScopeInterface::SCOPE_STORE;
                $scopeId = $observer->getStore();
            } else {
                $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                $scopeId = 0;
            }

            if ($this->helper->isModuleEnabled($scope, $scopeId)) {
                $this->onetrust->generateOauthTokenAndStoreInConfig($scope, $scopeId);
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $this;
    }
}
