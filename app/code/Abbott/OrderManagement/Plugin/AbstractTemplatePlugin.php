<?php

namespace Abbott\OrderManagement\Plugin;

use \Magento\Email\Model\AbstractTemplate;

class AbstractTemplatePlugin
{
    protected $currency;

    public function __construct(
        \Magento\Directory\Model\Currency $currency
    ) {
        $this->currency = $currency;
    }
    /* additional variables in email */
    public function beforeGetProcessedTemplate(AbstractTemplate $subject, $variables = [])
    {
        $variables['currency_symbol'] = $this->currency->getCurrencySymbol();
        return [$variables];
    }
}
