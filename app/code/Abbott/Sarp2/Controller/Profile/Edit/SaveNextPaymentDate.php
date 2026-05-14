<?php


namespace Abbott\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Model\DateTime\FormatConverter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Abbott\Sarp2\Helper\Data;
use Abbott\Sarp2\Helper\ChangeSubscription;
use Abbott\Subscriptionhistory\Helper\Data as HistoryDataLog;
use Laminas\Validator\StaticValidator;
/**
 * Class SaveNextPaymentDate
 * @package Aheadworks\Sarp2\Controller\Profile\Edit
 */
class SaveNextPaymentDate extends AbstractProfile
{
	public $historyDataLog;
 const CHANGE_SUBSCIPTION_PLAN_EVENT = "subscription_payment_date_change";

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var FormatConverter
     */
    private $dateFormatConverter;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    private $helper;

    private $updateSubscribe;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param FormKeyValidator $formKeyValidator
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProfileManagementInterface $profileManagement
     * @param ResolverInterface $localeResolver
     * @param TimezoneInterface $localeDate
     * @param FormatConverter $dateFormatConverter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        FormKeyValidator $formKeyValidator,
        CustomerRepositoryInterface $customerRepository,
        ProfileManagementInterface $profileManagement,
        ResolverInterface $localeResolver,
        TimezoneInterface $localeDate,
        FormatConverter $dateFormatConverter,
        Data $helper,
        ChangeSubscription $updateSubscribe,
		HistoryDataLog $historyDataLog
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
        $this->profileManagement = $profileManagement;
        $this->localeResolver = $localeResolver;
        $this->localeDate = $localeDate;
        $this->dateFormatConverter = $dateFormatConverter;
        $this->helper = $helper;
        $this->updateSubscribe = $updateSubscribe;
        $this->historyDataLog = $historyDataLog;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
		$profileId = $this->getRequest()->getParam('profile_id');
        if ($data) {
            try {
                $this->validate($data);
				$profile = $this->performSave($data);

                if ($this->helper->getUpdateMailEnabled()) {
                    $this->updateSubscribe->updateSubscriptionNotification();
                }

                $this->messageManager->addSuccessMessage(__('Next Payment Date has been successfully changed.'));
                return $resultRedirect->setPath('*/*/index', ['profile_id' => $profile->getProfileId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while changed the Next Payment Date.')
                );
            }
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    protected function isActionAllowed()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->actionPermission->isEditNextPaymentDateActionAvailable($profileId);
    }

    /**
     * Validate form
     *
     * @param array $data
     * @throws LocalizedException
     */
    private function validate($data)
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
        }
        $nextPaymentDate = $data['next-payment-date'];
        $zendValidateArgs = [
            'format' => $this->dateFormatConverter->convertToDateTimeFormat(),
            'locale' => $this->localeResolver->getLocale()
        ];
        if (!StaticValidator::execute($nextPaymentDate, 'Date', $zendValidateArgs)) {
            throw new  LocalizedException(__('Next Payment Date is incorrect.'));
        }
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function performSave($data)
    {
        $profile = $this->getProfile();
		$oldPaymentDate = $this->historyDataLog->getNextPaymentInfo($profile->getProfileId());
        $nextPaymentDate = $data['next-payment-date'];

        $newNextPaymentDate = \DateTime::createFromFormat(
            $this->dateFormatConverter->convertToDateTimeFormat(),
            $nextPaymentDate,
            new \DateTimeZone($this->localeDate->getConfigTimezone())
        );
        $newNextPaymentDate = $this->localeDate->date($newNextPaymentDate, null, false);
        $newNextPaymentDate = $newNextPaymentDate->format(DateTime::DATETIME_PHP_FORMAT);

        $result = $this->profileManagement->changeNextPaymentDate($profile->getProfileId(), $newNextPaymentDate);
		 if(!empty($result) && $this->historyDataLog->getSubscriptionHistoryStatus($result->getStoreId()) && strtotime($oldPaymentDate)!= strtotime($nextPaymentDate)){
			$oldData = [self::CHANGE_SUBSCIPTION_PLAN_EVENT => [ $profile->getProfileId() => $oldPaymentDate]];
			$newData = [self::CHANGE_SUBSCIPTION_PLAN_EVENT => [ $profile->getProfileId() => $newNextPaymentDate]];
			$this->historyDataLog->prepareFrontendData($result, self::CHANGE_SUBSCIPTION_PLAN_EVENT, $oldData, $newData);
		}
		return $result;
    }
}
