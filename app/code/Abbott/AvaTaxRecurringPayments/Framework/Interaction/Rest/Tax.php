<?php


namespace Abbott\AvaTaxRecurringPayments\Framework\Interaction\Rest;


use Avalara\TransactionBuilder;
use Magento\Framework\DataObject;

class Tax extends \Avalara\AvaTax\Framework\Interaction\Rest\Tax
{
    /**
     * Set transaction-level fields for request
     *
     * @param TransactionBuilder $transactionBuilder
     * @param DataObject $request
     */
    protected function setTransactionDetails($transactionBuilder, $request)
    {

        if ($request->hasExemptionNo() && $request->getExemptionNo() != "") {
            $transactionBuilder->withExemptionNo($request->getExemptionNo());
        }
        return parent::setTransactionDetails($transactionBuilder, $request);
    }

}
