<?php

declare(strict_types=1);

namespace Abbott\KountCustomFields\Plugin\Gateway\Braintree\Request;

use Abbott\KountCustomFields\Helper\Data;
use Aheadworks\Sarp2\Gateway\Braintree\SubjectReaderFactory;
use Exception;
use Psr\Log\LoggerInterface;

class PaymentDataBuilder
{
    public $subjectReaderFactory;
    /**
     * @var Data
     */
    public Data $customHelper;
    public $logger;
    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param Data $customHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubjectReaderFactory $subjectReaderFactory,
        Data $customHelper,
        LoggerInterface $logger
    ) {
        $this->subjectReaderFactory = $subjectReaderFactory;
        $this->customHelper = $customHelper;
        $this->logger = $logger;
    }

    /**
     * @param \Aheadworks\Sarp2\Gateway\Braintree\Request\PaymentDataBuilder $subject
     * @param array $result
     * @param array $buildSubject
     * @return array
     */
    public function afterBuild(
        \Aheadworks\Sarp2\Gateway\Braintree\Request\PaymentDataBuilder $subject,
        $result,
        $buildSubject
    ): array
    {
        try {
            $subjectReader = $this->subjectReaderFactory->getInstance();
            $paymentDO = $subjectReader->readPayment($buildSubject);
            $result['customFields'] = $this->customHelper->getCustomFieldsForSarp2($paymentDO);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $result;
    }
}
