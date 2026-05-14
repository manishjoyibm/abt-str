<?php

namespace Abbott\Sarp2\Engine\Profile\Action\Type\ChangeNextPaymentDate;

use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Model\Config;
use Magento\Framework\Stdlib\DateTime;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Laminas\Validator\StaticValidator;

/**
 * Class Applier
 * @package Abbott\Sarp2\Engine\Profile\Action\Type\ChangeNextPaymentDate
 */
class Applier extends \Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeNextPaymentDate\Applier
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Manager
     */
    private $notificationManager;

    /**
     * @param ResultFactory $validationResultFactory
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param Config $config
     * @param Manager $notificationManager
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        Config $config,
        Manager $notificationManager
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->config = $config;
        $this->notificationManager = $notificationManager;
        parent::__construct(
            $validationResultFactory, 
            $paymentsList, 
            $paymentPersistence, 
            $config, 
            $notificationManager
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ProfileInterface $profile, ActionInterface $action)
    {
        $newNextPaymentDate = $action->getData()->getNewNextPaymentDate();

        $zendValidateArgs = ['format' => DateTime::DATETIME_PHP_FORMAT];
        if (!StaticValidator::execute($newNextPaymentDate, 'Date', $zendValidateArgs)) {
            $isValid = false;
            $message = 'Next Payment Date is incorrect.';
        } else {
            $isValid = true;
            $newNextPaymentDate = new \DateTime($newNextPaymentDate);
            $newNextPaymentDate->setTime(0, 0, 0);
            $earliestNextPaymentDate = new \DateTime('now');
            $earliestNextPaymentDate->setTime(0, 0, 0);
            $earliestNextPaymentDate
                ->modify('+' . $this->config->getEarliestNextPaymentDate($profile->getStoreId()) . 'days');

            $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
            foreach ($payments as $payment) {
                if ($newNextPaymentDate < $earliestNextPaymentDate) {
                    $isValid = false;
                    $message = 'Next Payment Date must be in the future.';
                }

                if ($payment->getType() == PaymentInterface::TYPE_LAST_PERIOD_HOLDER) {
                    $isValid = false;
                    $message = 'Next Payment date cannot be changed after all payments are done.';
                }
            }
        }

        $resultData = ['isValid' => $isValid];
        if (!$isValid) {
            $resultData['message'] = $message;
        }
        return $this->validationResultFactory->create($resultData);
    }
}
