<?php

namespace Abbott\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierPool;
use Aheadworks\Sarp2\Engine\Profile\Action\CompositeDetector;
use Aheadworks\Sarp2\Model\Payment\Checker\OfflinePayment;
use Laminas\Validator\StaticValidator;

/**
 * Class Validator
 * @package Abbott\Sarp2\Model\Profile
 */
class Validator extends \Aheadworks\Sarp2\Model\Profile\Validator
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
        parent::__construct($profileActionDetector, $actionApplierPool, $offlinePaymentChecker);
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

        if (!StaticValidator::execute($profile->getStoreId(), 'NotEmpty')) {
            $this->_addMessages(['Store Id is required.']);
        }
        if (!StaticValidator::execute($profile->getPlanDefinitionId(), 'NotEmpty')) {
            $this->_addMessages(['Plan definition Id is required.']);
        }
        if (!StaticValidator::execute($profile->getStartDate(), 'NotEmpty')) {
            $this->_addMessages(['Start date is required.']);
        }
        if (!$this->offlinePaymentChecker->check($profile->getPaymentMethod())
            && !StaticValidator::execute($profile->getPaymentTokenId(), 'NotEmpty')
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
