<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class OrderSource extends CustomField
{
    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::ORDERSOURCE;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $source = $this->helper->getOrderSource($paymentDO);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $source;
    }
}
