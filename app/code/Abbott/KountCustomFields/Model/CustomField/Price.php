<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class Price extends CustomField
{

    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::PRICE;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $price = $this->helper->getItemValue($paymentDO, 'price');
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $price;
    }
}
