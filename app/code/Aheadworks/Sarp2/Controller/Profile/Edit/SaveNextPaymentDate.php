<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Model\DateTime\FormatConverter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class SaveNextPaymentDate
 * @package Aheadworks\Sarp2\Controller\Profile\Edit
 */
class SaveNextPaymentDate extends AbstractProfile
{
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
        FormatConverter $dateFormatConverter
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
        $this->profileManagement = $profileManagement;
        $this->localeResolver = $localeResolver;
        $this->localeDate = $localeDate;
        $this->dateFormatConverter = $dateFormatConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $this->validate($data);
                $profile = $this->performSave($data);
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
            'format' => $this->dateFormatConverter->convertToJsCalendarFormat(),
            'locale' => $this->localeResolver->getLocale()
        ];
        if (!\Zend_Validate::is($nextPaymentDate, 'Date', $zendValidateArgs)) {
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
        $nextPaymentDate = $data['next-payment-date'];
        $newNextPaymentDate = \DateTime::createFromFormat(
            $this->dateFormatConverter->convertToDateTimeFormat(),
            $nextPaymentDate,
            new \DateTimeZone($this->localeDate->getConfigTimezone())
        );
        $newNextPaymentDate = $this->localeDate->date($newNextPaymentDate, null, false);
        $newNextPaymentDate = $newNextPaymentDate->format(DateTime::DATETIME_PHP_FORMAT);

        return $this->profileManagement->changeNextPaymentDate($profile->getProfileId(), $newNextPaymentDate);
    }
}
