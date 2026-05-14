<?php
declare(strict_types=1);

namespace Abbott\Mfa\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\TwoFactorAuth\Api\TfaInterface;
use Magento\TwoFactorAuth\Api\TfaSessionInterface;
use Magento\TwoFactorAuth\Api\UserConfigRequestManagerInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\TwoFactorAuth\Controller\Adminhtml\Tfa\Requestconfig;
use Magento\TwoFactorAuth\Controller\Adminhtml\Tfa\Index;
use Magento\Authorization\Model\UserContextInterface;
use Magento\TwoFactorAuth\Model\UserConfig\HtmlAreaTokenVerifier;
use Magento\TwoFactorAuth\Observer\ControllerActionPredispatch as PredispatchControllerAction;

class ControllerActionPredispatch extends PredispatchControllerAction implements ObserverInterface
{
    
    public $scopeConfig;
    /**
     * Store Config 2FA path
     */
    private const MFA_USERROLE = 'twofactorauth/general/user_roles';

    /**
     * @var TfaInterface
     */
    private TfaInterface $tfa;

    /**
     * @var ActionFlag
     */
    private ActionFlag $actionFlag;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @var TfaSessionInterface
     */
    private TfaSessionInterface $tfaSession;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var UserConfigRequestManagerInterface
     */
    private UserConfigRequestManagerInterface|configRequestManager $configRequestManager;

    /**
     * @var AuthorizationInterface
     */
    private AuthorizationInterface $authorization;

    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @var HtmlAreaTokenVerifier
     */
    private HtmlAreaTokenVerifier $tokenManager;

    private $action;

    /**
     * ControllerActionPredispatch constructor.
     *
     * @param TfaInterface                      $tfa
     * @param ActionFlag                        $actionFlag
     * @param UrlInterface                      $url
     * @param Session                           $session
     * @param TfaSessionInterface               $tfaSession
     * @param ScopeConfigInterface              $scopeConfig
     * @param UserConfigRequestManagerInterface $configRequestManager
     * @param AuthorizationInterface            $authorization
     * @param UserContextInterface              $userContext
     * @param HtmlAreaTokenVerifier             $tokenManager
     */
    public function __construct(
        TfaInterface $tfa,
        ActionFlag $actionFlag,
        UrlInterface $url,
        Session $session,
        TfaSessionInterface $tfaSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        UserConfigRequestManagerInterface $configRequestManager,
        AuthorizationInterface $authorization,
        UserContextInterface $userContext,
        HtmlAreaTokenVerifier $tokenManager
    ) {
        $this->tfa = $tfa;
        $this->actionFlag = $actionFlag;
        $this->url = $url;
        $this->tfaSession = $tfaSession;
        $this->session = $session;
        $this->scopeConfig = $scopeConfig;
        $this->configRequestManager = $configRequestManager;
        $this->authorization = $authorization;
        $this->userContext = $userContext;
        $this->tokenManager = $tokenManager;
        parent::__construct(
            $tfa,
            $tfaSession,
            $configRequestManager,
            $tokenManager,
            $actionFlag,
            $url,
            $authorization,
            $userContext
        );
    }

    /**
     * Get current user
     *
     * @return \Magento\User\Model\User|null
     */
    private function getUser()
    {
        return $this->session->getUser();
    }

    /**
     * Redirect URL
     *
     * @param  string $url
     * @return void
     */
    private function redirect(string $url): void
    {
        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        $this->action->getResponse()->setRedirect($this->url->getUrl($url));
    }

    /**
     * Execute function
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $controllerAction AbstractAction
        */
        $controllerAction = $observer->getEvent()->getData('controller_action');
        $this->action = $controllerAction;
        $fullActionName = $observer->getEvent()
            ->getData('request')->getFullActionName();
        $userId = $this->userContext->getUserId();

        $user = $this->getUser();

        $this->tokenManager->readConfigToken();

        $rolesconfig = $this->scopeConfig->getValue(self::MFA_USERROLE);

        if (in_array($fullActionName, $this->tfa->getAllowedUrls(), true)
            || empty($rolesconfig)
        ) {
            //Actions that are used for 2FA must remain accessible.
            return;
        }

        $roles = explode(",", $rolesconfig ?? '');
        if ($user) {
            $role = $user->getRole()->getData();

            if (in_array($role['role_id'], $roles)) {
                $configurationStillRequired = $this->configRequestManager->isConfigurationRequiredFor(
                    $userId
                );
                $toActivate = $this->tfa->getProvidersToActivate($userId);
                $toActivateCodes = [];
                foreach ($toActivate as $toActivateProvider) {
                    $toActivateCodes[] = $toActivateProvider->getCode();
                }
                $accessGranted = $this->tfaSession->isGranted();

                if (!$accessGranted && $configurationStillRequired) {
                    //User needs special link with a token to be allowed to configure 2FA
                    if ($this->authorization->isAllowed(
                        Requestconfig::ADMIN_RESOURCE
                    )
                    ) {
                        $this->redirect('tfa/tfa/requestconfig');
                    } else {
                        $this->redirect('tfa/tfa/accessdenied');
                    }
                } else {
                    if (!$accessGranted) {
                        if ($this->authorization->isAllowed(
                            Index::ADMIN_RESOURCE
                        )
                        ) {
                            $this->redirect('tfa/tfa/index');
                        } else {
                            $this->redirect('tfa/tfa/accessdenied');
                        }
                    }
                }
            }
        }
    }
}
