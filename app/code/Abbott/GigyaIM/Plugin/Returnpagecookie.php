<?php

namespace Abbott\GigyaIM\Plugin;

use Abbott\AwsLambda\Logger\Log as Logger;
use Abbott\GigyaIM\Helper\Data as GigyaHelper;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Rma\Model\Rma;
use Magento\Store\Model\StoreManagerInterface;

class Returnpagecookie
{
    /**
     * @var logger
     */
    protected $logger;
    public const ABT_USR = 'abt_usr';

    /**
     * @var gigyaHelper
     */
    protected $gigyaHelper;

    /**
     * @var storeManager
     */
    protected $storeManager;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * @var AccountHelper
     */
    public $myaccounthelper;

    /**
     * CoustomerAttributePlugin constructor.
     *
     * @param Logger $logger
     * @param GigyaHelper $gigyaHelper
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param AccountHelper $myaccounthelper
     */
    public function __construct(
        Logger $logger,
        GigyaHelper $gigyaHelper,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        \Abbott\MyAccount\Helper\Data $myaccounthelper
    ) {
        $this->logger = $logger;
        $this->gigyaHelper = $gigyaHelper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->myaccounthelper = $myaccounthelper;
    }

    /**
     * Before plugin for SaveRma method.
     *
     * @param Rma $result
     * @return Rma
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function afterSaveRma(
        Rma $result
    ) {
        if ($this->storeManager->getStore()->getCode() == AccountHelper::NEW_SIM_STORE_CODE) {
            if ($this->gigyaHelper->getCustomCookie(self::ABT_USR)) {
                $abt_usr = json_decode($this->gigyaHelper->getCustomCookie(self::ABT_USR), true);
                $abt_usr['link_hide']['returns'] = 1;
            }
            $this->gigyaHelper->setCookie(self::ABT_USR, json_encode($abt_usr));
            if ($this->myaccounthelper->getConfigGoogleAnalyticsEnable()) {
                $this->customerSession->setReturnsave(1);
            }
        }
        return $result;
    }
}
