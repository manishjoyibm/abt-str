<?php
namespace Abbott\AbbottReport\Model;

use Exception;
use Magento\Framework\Message\MessageInterface;
use Magento\Logging\Model\Event;
use Magento\Logging\Model\Handler\Controllers as ControllersLoggingHandler;
use Magento\Logging\Model\Handler\ControllersFactory as LoggingHandlerFactory;

/**
 * Logging processor model
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Processor extends \Magento\Logging\Model\Processor
{
    /**
     * @var actionLogger
     */
    protected $actionLogger;

    public function __construct(
        \Magento\Logging\Model\Config $config,
        \Magento\Logging\Model\Handler\Models $modelsHandler,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger,
        LoggingHandlerFactory $handlerControllersFactory,
        \Magento\Logging\Model\EventFactory $eventFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Logging\Model\Event\ChangesFactory $changesFactory,
        \Abbott\AbbottReport\Logger\AuditLogger $actionLogger,
        ControllersLoggingHandler $controllersHandler = null
    ) {
        $this->actionLogger = $actionLogger;
        parent::__construct(
            $config,
            $modelsHandler,
            $authSession,
            $messageManager,
            $objectManager,
            $logger,
            $handlerControllersFactory,
            $eventFactory,
            $request,
            $remoteAddress,
            $changesFactory,
            $controllersHandler
        );
    }
    /**
     * Post-dispatch action handler
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return $this|bool
     */
    public function logAction()
    {
        if (!$this->_initAction || !$this->_eventConfig) {
            return false;
        }

        if ($this->_actionName == 'denied') {
            $this->logDeniedAction();
            return $this;
        }

        if ($this->_skipNextAction) {
            return false;
        }

        $loggingEvent = $this->initLoggingEvent();
        $action = isset($this->_eventConfig['action']) ? $this->_eventConfig['action'] : '';
        $loggingEvent->setAction($action);
        $groupName = isset($this->_eventConfig['group_name']) ? $this->_eventConfig['group_name'] : '';
        $loggingEvent->setEventCode($groupName);

        try {
            if (!$this->callPostDispatchCallback($loggingEvent)) {
                return false;
            }

            /* Prepare additional info */
            if ($this->getCollectedAdditionalData()) {
                $loggingEvent->setAdditionalInfo($this->getCollectedAdditionalData());
            }
            $loggingEvent->save();

            /**** code changes Start****/
            $loggingEventData = $loggingEvent->getData();
            $logData = '';

            foreach ($loggingEventData as $columnKey => $columnValue) {
                $logData = $logData.$columnKey.'='.$columnValue.',';
            }

            $this->actionLogger->info($logData);

            /**** code changes End ****/

            $this->saveEventChanges($loggingEvent);

        } catch (Exception $e) {
            $this->_logger->critical($e);
            return false;
        }
        return $this;
    }

    /**
     * Initialize logging event
     *
     * @return Event
     */
    private function initLoggingEvent(): Event
    {
        $username = null;
        $userId = null;
        if ($this->_authSession->isLoggedIn()) {
            $userId = $this->_authSession->getUser()->getId();
            $username = $this->_authSession->getUser()->getUsername();
        }
        $errors = $this->messageManager->getMessages()->getErrors();
        $closure = function (MessageInterface $message) {
            return $message->toString();
        };
        /** @var \Magento\Logging\Model\Event $loggingEvent */
        $loggingEvent = $this->_eventFactory->create()->setData(
            [
                'ip' => $this->_remoteAddress->getRemoteAddress(),
                'x_forwarded_ip' => $this->_request->getServer('HTTP_X_FORWARDED_FOR'),
                'user' => $username,
                'user_id' => $userId,
                'is_success' => empty($errors),
                'fullaction' => $this->_initAction,
                'error_message' => implode("\n", array_map($closure, $errors)),
            ]
        );
        return $loggingEvent;
    }

    /**
     * Call post dispatch callback
     *
     * @param Event $loggingEvent
     * @return $this|bool
     */
    private function callPostDispatchCallback(Event $loggingEvent): bool|static
    {
        $handler = $this->_controllersHandler;
        $callback = 'postDispatchGeneric';

        if (isset($this->_eventConfig['post_dispatch'])) {
            $classPath = explode('::', $this->_eventConfig['post_dispatch']);
            if (count($classPath) == 2) {
                $handler = $this->_objectManager->get(str_replace('__', '/', $classPath[0]));
                $callback = $classPath[1];
            } else {
                $callback = $classPath[0];
            }
            if (!$handler || !$callback || !method_exists($handler, $callback)) {
                $this->_logger->critical(
                    new \Magento\Framework\Exception\LocalizedException(
                        __('Unknown callback function: %1::%2', $handler, $callback)
                    )
                );
            }
        }

        if (!$handler) {
            return false;
        }

        if (!$handler->{$callback}($this->_eventConfig, $loggingEvent, $this)) {
            return false;
        }
        return $this;
    }

    /**
     * Save event changes
     *
     * @param Event $loggingEvent
     * @return $this|bool
     * @throws Exception
     */
    private function saveEventChanges($loggingEvent): bool|static
    {
        if (!$loggingEvent->getId()) {
            return false;
        }
        foreach ($this->_eventChanges as $changes) {
            if ($changes && ($changes->getOriginalData() || $changes->getResultData())) {
                $changes->setEventId($loggingEvent->getId());
                $changes->save();
            }
        }
        return $this;
    }

}
