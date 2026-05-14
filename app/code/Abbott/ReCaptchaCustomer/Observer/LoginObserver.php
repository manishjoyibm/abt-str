<?php

namespace Abbott\ReCaptchaCustomer\Observer;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\RequestHandlerInterface;

class LoginObserver extends \Magento\ReCaptchaCustomer\Observer\LoginObserver
{
    private const IS_AEM_LOGIN_USER_KEY = 'recaptcha_frontend/type_for/isaemloginuserkey';
    /**
     * @var IsCaptchaEnabledInterface
     */
    private IsCaptchaEnabledInterface $isCaptchaEnabled;

    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $requestHandler;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var Url
     */
    private Url $url;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param IsCaptchaEnabledInterface $isCaptchaEnabled
     * @param RequestHandlerInterface $requestHandler
     * @param Session $session
     * @param Url $url
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        IsCaptchaEnabledInterface $isCaptchaEnabled,
        RequestHandlerInterface   $requestHandler,
        Session                   $session,
        Url                       $url,
        ScopeConfigInterface      $scopeConfig,
    ) {
        $this->isCaptchaEnabled = $isCaptchaEnabled;
        $this->requestHandler = $requestHandler;
        $this->session = $session;
        $this->url = $url;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($isCaptchaEnabled, $requestHandler, $session, $url);
    }

    /**
     * Execute function
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $key = 'customer_login';
        if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key)) {
            /** @var Action $controller */
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $isAEMLoginUserKey = trim((string)$this->scopeConfig->getValue(self::IS_AEM_LOGIN_USER_KEY));
            if ($request->getParam("is_aem_login_user_key") &&
                $isAEMLoginUserKey == $request->getParam("is_aem_login_user_key")
            ) {
                return;
            }
            $response = $controller->getResponse();
            $redirectOnFailureUrl = $this->session->getBeforeAuthUrl() ?: $this->url->getLoginUrl();

            $this->requestHandler->execute($key, $request, $response, $redirectOnFailureUrl);
        }
    }
}
