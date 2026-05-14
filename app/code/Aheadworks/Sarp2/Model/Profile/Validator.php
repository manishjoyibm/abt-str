<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierPool;
use Aheadworks\Sarp2\Engine\Profile\Action\CompositeDetector;
use Aheadworks\Sarp2\Model\Payment\Checker\OfflinePayment;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Profile
 */
class Validator extends AbstractValidator
{
    /**
     * @var CompositeDetector
     */
    private $profileActionDetector;

    /**
     * @var ApplierPool
     */
    private $actionApplierPool;

    /**
     * @var OfflinePayment
     */
    private $offlinePaymentChecker;

    /**
     * @param CompositeDetector $profileActionDetector
     * @param ApplierPool $actionApplierPool
     * @param OfflinePayment $offlinePaymentChecker
     */
    public function __construct(
        CompositeDetector $profileActionDetector,
        ApplierPool $actionApplierPool,
        OfflinePayment $offlinePaymentChecker
    ) {
        $this->profileActionDetector = $profileActionDetector;
        $this->actionApplierPool = $actionApplierPool;
        $this->offlinePaymentChecker = $offlinePaymentChecker;
    }

    /**
     * Returns true if and only if profile entity meets the validation requirements
     *
     * @param ProfileInterface $profile
     * @return bool
     */
    public function isValid($profile)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($profile->getStoreId(), 'NotEmpty')) {
            $this->_addMessages(['Store Id is required.']);
        }
        if (!\Zend_Validate::is($profile->getPlanDefinitionId(), 'NotEmpty')) {
            $this->_addMessages(['Plan definition Id is required.']);
        }
        if (!\Zend_Validate::is($profile->getStartDate(), 'NotEmpty')) {
            $this->_addMessages(['Start date is required.']);
        }
        if (!$this->offlinePaymentChecker->check($profile->getPaymentMethod())
            && !\Zend_Validate::is($profile->getPaymentTokenId(), 'NotEmpty')
        ) {
            $this->_addMessages(['Payment token Id is required.']);
        }

        $action = $this->profileActionDetector->detect($profile);
        if ($action) {
            $validationResult = $this->actionApplierPool->getApplier($action->getType())
                ->validate($profile, $action);
            if (!$validationResult->isValid()) {
                $this->_addMessages(['Profile action is not available: ' . $validationResult->getMessage()]);
            }
        }

        return empty($this->getMessages());
    }
}
