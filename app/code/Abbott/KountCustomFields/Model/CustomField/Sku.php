<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class Sku extends CustomField
{
    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::SKU;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $sku = $this->helper->getItemValue($paymentDO, 'sku');
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $sku;
    }
}
