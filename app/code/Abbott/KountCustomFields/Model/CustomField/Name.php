<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class Name extends CustomField
{
    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $name = $this->helper->getItemValue($paymentDO, 'name');
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $name;
    }
}
