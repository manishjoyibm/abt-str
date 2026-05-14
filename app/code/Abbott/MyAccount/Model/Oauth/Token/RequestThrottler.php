<?php

namespace Abbott\MyAccount\Model\Oauth\Token;

use Magento\Integration\Model\Oauth\Token\RequestLog\ReaderInterface as RequestLogReader;
use Magento\Integration\Model\Oauth\Token\RequestLog\WriterInterface as RequestLogWriter;
use Magento\Integration\Model\Oauth\Token\RequestLog\Config as RequestLogConfig;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Model for OAuth admin/customer token requests throttling.
 */
class RequestThrottler extends \Magento\Integration\Model\Oauth\Token\RequestThrottler
{
    /**
     * Web API user type
     */
    const USER_TYPE_CUSTOMER = 2;
    const USER_TYPE_ADMIN = 3;
   

    
    private $requestLogReader;


    /**
     * @var RequestLogConfig
     */
    private $requestLogConfig;

    /**
     * Initialize dependencies.
     *
     * @param RequestLogReader $requestLogReader
     * @param RequestLogWriter $requestLogWriter
     * @param RequestLogConfig $requestLogConfig
     */
    public function __construct(
        RequestLogReader $requestLogReader,
        RequestLogWriter $requestLogWriter,
        RequestLogConfig $requestLogConfig
    ) {
        $this->requestLogReader = $requestLogReader;
        $this->requestLogConfig = $requestLogConfig;
        
        parent::__construct($requestLogReader, $requestLogWriter, $requestLogConfig);
    }

    /**
     * Throw exception if user account is currently locked because of too many failed authentication attempts.
     *
     * @param string $userName
     * @param int $userType
     * @return void
     * @throws AuthenticationException
     */
    public function throttle($userName, $userType)
    {
        $count = $this->requestLogReader->getFailuresCount($userName, $userType);
        if ($count >= $this->requestLogConfig->getMaxFailuresCount()) {
            if ($userType == self::USER_TYPE_CUSTOMER) {
                throw new UserLockedException(
                    __(
                        'Your account is locked. Please wait and try again later.'
                    )
                );
            } else {
                throw new AuthenticationException(
                    __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                            . 'Please wait and try again later.'
                    )
                );
            }
        }
    }
}
