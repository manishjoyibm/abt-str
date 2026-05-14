<?php
namespace Abbott\AdminSessionAlert\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Auth\Session;

class SessionConfiguration implements ArgumentInterface
{
    /**
     * Admin session lifetime XML path
     */
    const XML_PATH_SESSION_LIFETIME = 'admin/security/session_lifetime';

    /**
     * Admin session popup enable  XML path
     */
    const XML_PATH_ENABLED = 'admin_session/timeout_warning/enabled';

    /**
     * Admin session warning timeout XML path
     */
    const XML_PATH_WARNING_TIME = 'admin_session/timeout_warning/warning_time';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Session
     */
    protected  $authSession;

    public function __construct(ConfigInterface $config, Session $authSession)
    {
        $this->config = $config;
        $this->authSession = $authSession;
    }

    /**
     * Get the admin session lifetime value
     *
     * @return int|null
     */
    public function getAdminSessionTimeout():?int
    {
        return $this->config->getValue(self::XML_PATH_SESSION_LIFETIME);
    }

    /**
     * Get the admin session warning Timeout value
     *
     * @return int|null
     */
    public function getWarningTimeOut():?int
    {
        return $this->config->getValue(self::XML_PATH_WARNING_TIME);
    }

    /**
     * Get the admin session login value
     *
     * @return boolean
     */
    public function isLoggedIn():bool
    {
        return $this->authSession->isLoggedIn();
    }

    /**
     * Get the admin session enable value
     *
     */
    public function isEnabled()
    {
        return $this->config->isSetFlag(self::XML_PATH_ENABLED);
    }
}
