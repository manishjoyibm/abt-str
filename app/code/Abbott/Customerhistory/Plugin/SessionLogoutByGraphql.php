<?php
declare(strict_types=1);

namespace Abbott\Customerhistory\Plugin;

use Magento\CustomerGraphQl\Model\Resolver\GenerateCustomerToken;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\JwtUserToken\Model\Reader as JwtReader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Abbott\Customerhistory\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Logger;


/**
 * After-plugin for GraphQL customer login token generator.
 * Stamps login time and token expiry into customer_log.
 */
class SessionLogoutByGraphql
{
    private DateTime $utcClock;
    private ResourceConnection $resource;
    private JwtReader $jwtReader;
    private ScopeConfigInterface $scopeConfig;
    private LoggerInterface $logger;
    private Data $helper;
    private $customerRepository;
    private Logger $customerLogger;


    public function __construct(
        DateTime $utcClock,
        ResourceConnection $resource,
        JwtReader $jwtReader,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        Data $helper,
        CustomerRepositoryInterface $customerRepository,
        Logger            $customerLogger
    ) {
        $this->utcClock   = $utcClock;
        $this->resource   = $resource;
        $this->jwtReader  = $jwtReader;
        $this->scopeConfig = $scopeConfig;
        $this->logger     = $logger;
        $this->helper     = $helper;
        $this->customerRepository = $customerRepository;
        $this->customerLogger  = $customerLogger;
    }

    /**
     * After resolver hook: runs after GraphQL generateCustomerToken().
     * IMPORTANT: Always return $result (GraphQL response).
     */
    public function afterResolve(
        GenerateCustomerToken $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            // Keep original return intact
            $token = is_array($result) ? ($result['token'] ?? null) : null;
            if (!$token) {
                return $result;
            }
            // Feature toggles via helper
            if (!$this->helper->isEnabled() || !$this->helper->isSessionEnabled()) {
                return $result;
            }

            // The email is provided in the GraphQL mutation arguments
            $email = $args['email'] ?? null;

            if ($email) {
                try {
                    $customer = $this->customerRepository->get($email);
                    $customerId = $customer->getId();
                    // Your custom logic with $customerId here
                } catch (\Throwable $e) {
                // Do not break login flow; just log
                $this->logger->error('[SessionLogoutByGraphql] Email not found: ' . $e->getMessage());
            }
            }

            $customerLifetime = $this->helper->getJwtLifeTime();     // seconds
            $nowUtcTs         = (int)$this->utcClock->gmtTimestamp();     // current UTC
            $expiresUtcTs     = $nowUtcTs + $customerLifetime;            // absolute expiry
            $expiresUtcYmdHis = gmdate('Y-m-d H:i:s', $expiresUtcTs);     // MySQL datetime

            // Stamp `last_logout_at` as the computed expiry
            $this->customerLogger->log($customerId, ['last_logout_at' => $expiresUtcYmdHis]);
            // Stamp source
            $this->customerLogger->log($customerId, ['logout_source' => 'Session Logout']);
            $this->logger->info('Successfully Logged time => '. $expiresUtcYmdHis);
            } catch (\Throwable $e) {
                // Do not break login flow; just log
                $this->logger->error('[SessionLogoutByGraphql] Stamp failed: ' . $e->getMessage());
            }

        return $result;
    }

}