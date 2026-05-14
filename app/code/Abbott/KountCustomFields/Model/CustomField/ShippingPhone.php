<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class ShippingPhone extends CustomField
{

    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::SHIPPINGPHONE;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $order = $paymentDO->getOrder();
            $telephone = $order->getShippingAddress()->getTelephone();
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $telephone;
    }
}
