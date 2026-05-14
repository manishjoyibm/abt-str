<?php
namespace Abbott\Sarp2\Plugin\Model;

class Currency
{
    public function beforeFormatPrecision(\Magento\Directory\Model\Currency $subject, $price, $precision,
    $options = [],
    $includeContainer = true,
    $addBrackets = false)
    {
        $precision = 2;
        return [$price, $precision, $options, $includeContainer, $addBrackets];
    }
}