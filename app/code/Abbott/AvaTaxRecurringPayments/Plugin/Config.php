<?php
namespace Abbott\AvaTaxRecurringPayments\Plugin;

Class Config
{
    public function afterGetTextCaseMixed(\Avalara\AvaTax\Helper\Rest\Config $source, $result)
    {
        return \Avalara\TextCase::C_UPPER;
    }
}