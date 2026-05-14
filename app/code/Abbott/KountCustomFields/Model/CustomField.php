<?php

namespace Abbott\KountCustomFields\Model;

use Abbott\KountCustomFields\Helper\Daa;
use Abbott\KountCustomFields\Helper\Data;
use PayPal\Braintree\Model\CustomFields\CustomFieldInterface;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Psr\Log\LoggerInterface;

abstract class CustomField implements CustomFieldInterface
{
    /**
     * @var SubjectReader
     */
    protected SubjectReader $subjectReader;

    /**
     * @var Daa
     */
    protected Daa $helper;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param SubjectReader $subjectReader
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->helper = $helper;
        $this->logger = $logger;
    }
}
