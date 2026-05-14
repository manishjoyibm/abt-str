<?php
namespace Abbott\AbbottReport\Observer;

use Exception;
use Magento\Logging\Model\Config;
use Magento\Logging\Model\Event;

class AdminLogin
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\User\Model\User
     */
    protected $user;

    /**
     * @var Event
     */
    protected $event;

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var actionLogger
     */
    protected $actionLogger;

    /**
     * @param Config $config
     * @param \Magento\User\Model\User $user
     * @param Event $event
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Abbott\AbbottReport\Logger\AuditLogger $actionLogger
     */
    public function __construct(
        Config $config,
        \Magento\User\Model\User $user,
        Event $event,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Abbott\AbbottReport\Logger\AuditLogger $actionLogger
    ) {
        $this->config = $config;
        $this->user = $user;
        $this->event = $event;
        $this->request = $request;
        $this->remoteAddress = $remoteAddress;
        $this->actionLogger = $actionLogger;
    }

    /**
     * Log sign in attempt
     *
     * @param string $username
     * @param int $userId
     * @return Event
     * @throws Exception
     */
    public function logAdminLogin($username, $userId = null)
    {
        $eventCode = 'admin_login';
        if (!$this->config->isEventGroupLogged($eventCode)) {
            return;
        }
        $success = (bool)$userId;
        if (!$userId) {
            $userId = $this->user->loadByUsername($username)->getId();
        }
        $this->event->setData(
            [
                'ip' => $this->remoteAddress->getRemoteAddress(),
                'user' => $username,
                'user_id' => $userId,
                'is_success' => $success,
                'fullaction' => "{$this->request->getRouteName()}_{$this->request->getControllerName()}" .
                    "_{$this->request->getActionName()}",
                'event_code' => $eventCode,
                'action' => 'login',
            ]
        );
        $loggingEventData = $this->event->getData();
        $logData = '';
        foreach ($loggingEventData as $columnKey => $columnValue) {
            $logData = $logData.$columnKey.'='.$columnValue.',';
        }
        $this->actionLogger->info($logData);

        return $this->event->save();
    }
}
