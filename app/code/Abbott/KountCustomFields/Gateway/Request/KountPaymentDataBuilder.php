<?php

namespace Abbott\KountCustomFields\Gateway\Request;

use Kount\Kount360\Model\Config\Account as Config;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

class KountPaymentDataBuilder implements BuilderInterface
{
    /**
     * Additional data for Advanced Fraud Tools
     */
    const DEVICE_DATA = 'deviceData';

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(Config $config, SubjectReader $subjectReader)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $result = [];

        if (!$this->config->isAvailable()) {
            return $result;
        }

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = $payment->getAdditionalInformation();

        if (isset($data[DataAssignObserver::DEVICE_DATA])) {
            $result[self::DEVICE_DATA] = $data[DataAssignObserver::DEVICE_DATA];
        }

        return $result;
    }
}
