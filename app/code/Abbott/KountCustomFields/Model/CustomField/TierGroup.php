<?php

namespace Abbott\KountCustomFields\Model\CustomField;

use Abbott\KountCustomFields\Model\CustomField;
use Abbott\KountCustomFields\Helper\Data;
use Exception;

class TierGroup extends CustomField
{
    /**
     * @inheritdoc
     */
    public function getApiName() : string
    {
        return Data::TIERGROUP;
    }

    /**
     * @inheritdoc
     */
    public function getValue($buildSubject)
    {
        try {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $tierGroup = $this->helper->getTierGroup($paymentDO);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $tierGroup;
    }
}
