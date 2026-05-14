<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class Tax extends CustomField
{

    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::TAX;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $tax = $this->helper->getItemValue($paymentDO, 'tax_amount');
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $tax;
    }
}
