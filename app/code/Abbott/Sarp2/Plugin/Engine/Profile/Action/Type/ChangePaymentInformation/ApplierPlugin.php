<?php


namespace Abbott\Sarp2\Plugin\Engine\Profile\Action\Type\ChangePaymentInformation;

use Abbott\Sarp2\Model\PaymentChangeLog;
use Abbott\Sarp2\Model\PaymentChangeLogManager;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePaymentInformation\Applier;
use Abbott\Sarp2\Model\PaymentChangeLogFactory as PaymentChangeLogFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Validation\ValidationException;

/**
 * Class ApplierPlugin
 * @package Abbott\Sarp2\Plugin\Engine\Profile\Action\Type\ChangePaymentInformation
 */
class ApplierPlugin
{
    /**
     * @var ResultFactory
     */
    protected $validationResultFactory;

    /**
     * @var \Abbott\Sarp2\Helper\Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PaymentChangeLogManager
     */
    protected $paymentChangeLogManager;

    /**
     * ApplierPlugin constructor.
     * @param LoggerInterface $logger
     * @param ResultFactory $validationResultFactory
     * @param \Abbott\Sarp2\Helper\Data $helper
     * @param PaymentChangeLogManager $paymentChangeLogManager
     */
    public function __construct(
        LoggerInterface $logger,
        ResultFactory $validationResultFactory,
        \Abbott\Sarp2\Helper\Data $helper,
        PaymentChangeLogManager $paymentChangeLogManager
    ) {

        $this->logger = $logger;
        $this->validationResultFactory = $validationResultFactory;
        $this->helper = $helper;
        $this->paymentChangeLogManager = $paymentChangeLogManager;
    }

    /**
     * @param Applier $subject
     */
    public function afterApply(Applier $subject, $result, ProfileInterface $profile, ActionInterface $action) {
        try {
            $this->paymentChangeLogManager->addRecord($profile->getCustomerId(), false, $profile->getProfileId(), $profile->getPaymentTokenId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $result;
    }

    /**
     * @param Applier $subject
     * @param $result
     * @param ProfileInterface $profile
     * @param ActionInterface $action
     * @return mixed
     */
    public function afterValidate(Applier $subject, $result, ProfileInterface $profile, ActionInterface $action) {

        try {
            $this->paymentChangeLogManager->validateFailedPaymentChanges($profile->getCustomerId());
            $this->paymentChangeLogManager->validatePaymentChanges($profile->getCustomerId(), $profile->getProfileId());
            return $result;
        } catch (ValidationException $e) {
            $resultData = ['isValid' => false, 'message' => $e->getMessage()];
            return $this->validationResultFactory->create($resultData);
        } catch (\Exception $e) {
            return $result;
        }
    }

}
