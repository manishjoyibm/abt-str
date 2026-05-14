<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class Quantity extends CustomField
{

    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::QUANTITY;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $quantity = $this->helper->getItemValue($paymentDO, 'qty_ordered');
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $quantity;
    }
}
