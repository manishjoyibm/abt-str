<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Validator;

use Aheadworks\Sarp2\Gateway\StripePayments\SubjectReader;
use Aheadworks\Sarp2\Model\Adapter\StripePayments\Response;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class ResponseCodeValidator
 * @package Aheadworks\Sarp2\Gateway\StripePayments\Validator
 */
class ResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * Performs validation of result status
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        /** @var Response $response */
        $response = $this->subjectReader->readResponseObject($validationSubject);

        $isValid = false;
        $errorMessages = [];

        if ($response->getStatus() == 'succeeded'
            || $response->getStatus() == 'requires_capture'
        ) {
            $isValid = true;
        }

        return $this->createResult($isValid, $errorMessages);
    }
}
