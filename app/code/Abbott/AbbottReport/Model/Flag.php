<?php
namespace Abbott\AbbottReport\Model;

class Flag extends \Magento\Framework\Flag
{
    const REPORT_PRODUCT_SUBSCRIPTION_FLAG_CODE = 'report_subscription_product_aggregated';
    const REPORT_CUSTOMER_SUBSCRIPTION_FLAG_CODE = 'report_subscription_customer_aggregated';

    /**
     * Setter for flag code.
     *
     * @codeCoverageIgnore
     *
     * @param string $code
     *
     * @return $this
     */
    public function setReportFlagCode($code)
    {
        $this->_flagCode = $code;

        return $this;
    }
}
