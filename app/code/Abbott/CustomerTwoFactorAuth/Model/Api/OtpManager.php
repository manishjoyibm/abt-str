<?php
namespace Abbott\CustomerTwoFactorAuth\Model\Api;

use Abbott\CustomerTwoFactorAuth\Api\OtpManagerInterface;
use Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpRequestInterfaceFactory;
use Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpResponseInterfaceFactory;
use Abbott\CustomerTwoFactorAuth\Api\VerifyOtpRequestInterfaceFactory;
use Abbott\CustomerTwoFactorAuth\Api\GetCustomerAttributeFactory;
use Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterfaceFactory;
use Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface;
use Abbott\CustomerTwoFactorAuth\Api\GenerateOtpInterface;
use Abbott\CustomerTwoFactorAuth\Api\SendMessageInterface;
use Abbott\CustomerTwoFactorAuth\Helper\Data as DataHelper;
use Abbott\CustomerTwoFactorAuth\Api\OtpRepositoryInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\AuthenticationException;

/**
 * Class OtpManager
 */
class OtpManager implements OtpManagerInterface
{
    public const DATE_FORMAT = "Y-m-d H:i:s";

    /**
     * @var SendAndSaveOtpRequestFactory
     */
    private $sendAndSaveOtpRequestFactory;

    /**
     * @var SendAndSaveOtpResponseFactory
     */
    private $sendAndSaveOtpResponseFactory;

    /**
     * @var VerifyOtpRequestFactory
     */
    private $verifyOtpRequestFactory;

    /**
     * @var GetCustomerAttribute
     */
    private $getCustomerAttributeFactory;

    /**
     * @var GenerateOtpInterface
     */
    private $generateOtp;

    /**
     * @var SendMessageInterface
     */
    private $sendMessage;

    /**
     * @var OtpInterfaceFactory
     */
    private $otpInterfaceFactory;

    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * @var OtpRepositoryInterface
     */
    private $otpRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var PsrLogger
     */
    private $logger;

    /**
     * @var IS_ENABLE
     */
    public const IS_ENABLE = 'allow_2FA';

    protected $accountManagement;
    protected $customerRepository;

    /**
     * @param SendAndSaveOtpRequestFactory   $sendAndSaveOtpRequestFactory
     * @param SendAndSaveOtpResponseFactory  $sendAndSaveOtpResponseFactory
     * @param VerifyOtpRequestFactory        $verifyOtpRequestFactory
     * @param GetCustomerAttribute           $getCustomerAttributeFactory
     * @param GenerateOtpInterface           $generateOtp
     * @param SendMessageInterface           $sendMessage
     * @param OtpInterfaceFactory            $otpInterfaceFactory
     * @param DataHelper                     $helper
     * @param OtpRepositoryInterface         $otpRepository
     * @param SearchCriteriaBuilder          $searchCriteriaBuilder
     * @param FilterBuilder                  $filterBuilder
     * @param FilterGroupBuilder             $filterGroupBuilder
     * @param DateTime                       $dateTime
     * @param PsrLogger                      $logger
     * @param AccountManagementInterface     $accountManagement
     * @param CustomerRepositoryInterface    $customerRepository
     */
    public function __construct(
        SendAndSaveOtpRequestFactory $sendAndSaveOtpRequestFactory,
        SendAndSaveOtpResponseFactory $sendAndSaveOtpResponseFactory,
        VerifyOtpRequestFactory $verifyOtpRequestFactory,
        GetCustomerAttribute $getCustomerAttributeFactory,
        GenerateOtpInterface $generateOtp,
        SendMessageInterface $sendMessage,
        OtpInterfaceFactory $otpInterfaceFactory,
        DataHelper $helper,
        OtpRepositoryInterface $otpRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        DateTime $dateTime,
        PsrLogger $logger,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
    ) {
        $this->sendAndSaveOtpRequestFactory = $sendAndSaveOtpRequestFactory;
        $this->sendAndSaveOtpResponseFactory = $sendAndSaveOtpResponseFactory;
        $this->verifyOtpRequestFactory = $verifyOtpRequestFactory;
        $this->getCustomerAttributeFactory = $getCustomerAttributeFactory;
        $this->generateOtp = $generateOtp;
        $this->sendMessage = $sendMessage;
        $this->otpInterfaceFactory = $otpInterfaceFactory;
        $this->helper = $helper;
        $this->otpRepository = $otpRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $sendAndSaveOtpRequest
     * @return \Abbott\CustomerTwoFactorAuth\Api\SendAndSaveOtpResponseInterface|SendAndSaveOtpResponse|array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendAndSaveOtp($sendAndSaveOtpRequest)
    {
        if (!$this->helper->isModuleEnabled()) {
            throw new WebapiException(__("Unauthorized"), 1, WebapiException::HTTP_UNAUTHORIZED);
        }

        // EMAIL-based scope instead of IP-based
        $otpsByEmail = $this->getOtpsByEmail($sendAndSaveOtpRequest->getEmail())->getItems();

        $lockedUntil = (int)$this->helper->getLockedUntill(); // minutes
        $expiryLimitMinutes = (int)$this->helper->getExpiryLimit();

        $lockPeriodWithTimerExpire = $lockedUntil + $expiryLimitMinutes;

        $lockWindowStart = $this->dateTime->gmtDate(
            self::DATE_FORMAT,
            strtotime('-' . $lockPeriodWithTimerExpire . ' minutes')
        );
        $currentTime = $this->dateTime->gmtDate(self::DATE_FORMAT);
        $response = $this->sendAndSaveOtpResponseFactory->create();

        $otpLimit = (int)$this->helper->getOtpLimit();

        foreach ($otpsByEmail as $otpItem) {
            $updatedAt = date(self::DATE_FORMAT, strtotime($otpItem->getUpdatedAt()));
            $expiredTime = date(
                self::DATE_FORMAT,
                strtotime('+' . $expiryLimitMinutes . ' minutes', strtotime($updatedAt))
            );
            $times = (int)$otpItem->getTimes();

            // If an OTP is still valid (not expired), do not send another one
            if ($expiredTime >= $currentTime) {
                return $response->setSuccess(false)
                    ->setMessage(__('OTP has already been sent. Please wait ' . $expiryLimitMinutes . ' minute(s) before requesting again.'))
                    ->setOtp($otpItem->getOtp())
                    ->setAttempt($times)
                    ->setLimit($otpLimit)
                    ->setExpireTimerValue($expiryLimitMinutes)
                    ->setLockTime($lockedUntil)
                    ->setValue(2)
                    ->setExpiryMessage(__('This code will expire in ' . $expiryLimitMinutes . ' minutes.'));
            }

            // If limit reached and still within lock window → BLOCK
           
            if ($otpLimit && $times >= $otpLimit && $updatedAt >= $lockWindowStart) {
                return $response->setSuccess(false)
                    ->setMessage(__('You’ve reached the maximum of ' . $otpLimit . ' attempts. Please wait ' . $lockedUntil . ' minutes before trying again.'))
                    ->setOtp($otpItem->getOtp())
                    ->setAttempt($times)
                    ->setLimit($otpLimit)
                    ->setExpireTimerValue($expiryLimitMinutes)
                    ->setLockTime($lockedUntil)
                    ->setValue(3)
                    ->setExpiryMessage(__('This code will expire in ' . $expiryLimitMinutes . ' minutes.'));
            }
        }

        // Reuse existing row for this email if present
        $otp = $this->otpInterfaceFactory->create();
        if (count($otpsByEmail) > 0) {
            $otp = array_shift($otpsByEmail);
        }

        $existingTimes = (int)$otp->getTimes();
        $updatedAtRaw = $otp->getUpdatedAt() ? $otp->getUpdatedAt() : '1970-01-01 00:00:00';
        $updatedAtTs = strtotime($updatedAtRaw);
        $lockWindowStartTs = strtotime('-' . $lockPeriodWithTimerExpire . ' minutes', strtotime($currentTime));
      
        // Final guard: If limit reached and still in lock window → BLOCK
        if ($otpLimit && $existingTimes == $otpLimit && $updatedAtTs >= $lockWindowStartTs) {
            return $response->setSuccess(false)
                ->setMessage(__('You’ve reached the maximum of ' . $otpLimit . ' attempts. Please wait ' . $lockedUntil . ' minutes before trying again.'))
                ->setOtp($otp->getOtp())
                ->setAttempt($existingTimes)
                ->setLimit($otpLimit)
                ->setExpireTimerValue($expiryLimitMinutes)
                ->setLockTime($lockedUntil)
                ->setValue(3)
                ->setExpiryMessage(__('This code will expire in ' . $expiryLimitMinutes . ' minutes.'));
        }

        // If lock window has passed and limit had been reached, reset counter; otherwise, increment
        $setTimes = ($otpLimit && $existingTimes >= $otpLimit && $updatedAtTs < $lockWindowStartTs) ? 1 : ($existingTimes + 1);

        // Save strictly by email (IP not used to scope records anymore)
        $otp->setEmail($sendAndSaveOtpRequest->getEmail())
            ->setOtp($this->generateOtp->execute())
            ->setTimes($setTimes)
            ->setUpdatedAt($currentTime);

        try {
            $this->otpRepository->save($otp);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new WebapiException(__("Some error occurred while processing the request"), 1, WebapiException::HTTP_INTERNAL_ERROR);
        }

        if (!$this->sendMessage->sendOtpEmail($otp)) {
            throw new WebapiException(__("Some error occurred while processing the request."), 1, WebapiException::HTTP_INTERNAL_ERROR);
        }

        return $response->setSuccess(true)
            ->setMessage(__('For your security, a one-time auth code has been sent to ' . $otp->getEmail() . ' to confirm your account.'))
            ->setOtp($otp->getOtp())
            ->setAttempt($otp->getTimes())
            ->setLimit($otpLimit)
            ->setExpireTimerValue($expiryLimitMinutes)
            ->setLockTime($lockedUntil)
            ->setValue(1)
            ->setExpiryMessage(__('This code will expire in ' . $expiryLimitMinutes . ' minutes.'));
    }

    /**
     * @param $verifyOtpRequest
     * @return array
     */
    public function verifyOtp($verifyOtpRequest)
    {
        $result = [];
        $currentTime = $this->dateTime->gmtDate(self::DATE_FORMAT);
        $otps = $this->getOtpByEmailOtp($verifyOtpRequest)->getItems();

        if (count($otps) > 0) {
            foreach ($otps as $otp) {
                $updatedAt = date(self::DATE_FORMAT, strtotime($otp->getUpdatedAt()));
                $expiredTime = date(self::DATE_FORMAT, strtotime('+' . $this->helper->getExpiryLimit() . ' minutes', strtotime($updatedAt)));

                if ($currentTime >= $expiredTime) {
                    $result = [
                        'success' => false,
                        'message' => 'Invalid code',
                        'value'   => 1
                    ];
                } else {
                    // Email-based delete: remove all OTPs for this email after a successful verification
                    $this->deleteOtpsByEmail($verifyOtpRequest->getEmail());
                    $result = [
                        'success' => true,
                        'message' => 'Two Factor Authentication has been Verified.',
                        'value'   => 2
                    ];
                }
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'Invalid code',
                'value'   => 1
            ];
        }

        return $result;
    }

    /**
     * Email + OTP lookup for verification
     * @param $verifyOtpRequest
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpSearchResultsInterface
     */
    private function getOtpByEmailOtp($verifyOtpRequest)
    {
        $filterGroups = [];

        $emailFilter = $this->filterBuilder->create()
            ->setField(OtpInterface::EMAIL)
            ->setConditionType('eq')
            ->setValue($verifyOtpRequest->getEmail());
        $filterGroups[] = $this->filterGroupBuilder->create()
            ->setFilters([$emailFilter]);

        $otpFilter = $this->filterBuilder->create()
            ->setField(OtpInterface::OTP)
            ->setConditionType('eq')
            ->setValue($verifyOtpRequest->getOtp());
        $filterGroups[] = $this->filterGroupBuilder->create()
            ->setFilters([$otpFilter]);

        $searchCriteria = $this->searchCriteriaBuilder->setFilterGroups($filterGroups)->create();
        return $this->otpRepository->getList($searchCriteria);
    }

    /**
     * EMAIL-based search for send/save flow
     * @param string $email
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpSearchResultsInterface
     */
    private function getOtpsByEmail(string $email)
    {
        $filterGroups = [];

        $emailFilter = $this->filterBuilder->create()
            ->setField(OtpInterface::EMAIL)
            ->setConditionType('eq')
            ->setValue($email);
        $filterGroups[] = $this->filterGroupBuilder->create()
            ->setFilters([$emailFilter]);

        $searchCriteria = $this->searchCriteriaBuilder
            ->setFilterGroups($filterGroups)
            ->create();

        return $this->otpRepository->getList($searchCriteria);
    }

    /**
     * Delete all OTP rows for an email (email-based delete)
     * @param string $email
     * @return void
     */
    private function deleteOtpsByEmail(string $email): void
    {
        $items = $this->getOtpsByEmail($email)->getItems();
        foreach ($items as $item) {
            try {
                $this->otpRepository->delete($item);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Failed to delete OTP for %s: %s', $email, $e->getMessage()));
            }
        }
    }

    /**
     * @param $request
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAttribute($request)
    {
        if (!$this->helper->isModuleEnabled()) {
            return false;
        }

        $email = $request->getEmail();
        $pass  = $request->getPass();
        try {
            // This will throw an exception if authentication fails
            $customer = $this->accountManagement->authenticate($email, $pass);
            $isEnable = $customer->getCustomAttribute(self::IS_ENABLE);
            return $isEnable !== null ? (bool) $isEnable->getValue() : false;
        } catch (AuthenticationException $e) {
            return false; // Invalid credentials
        } catch (\Exception $e) {
            // Handle other errors
            return false;
        }
    }
}