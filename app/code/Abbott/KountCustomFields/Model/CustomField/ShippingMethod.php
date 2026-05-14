<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class ShippingMethod extends CustomField
{

    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::SHIPPINGMETHOD;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $shippingmethod = $paymentDO->getPayment()->getOrder()->getShippingDescription();
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $shippingmethod;
    }
}
