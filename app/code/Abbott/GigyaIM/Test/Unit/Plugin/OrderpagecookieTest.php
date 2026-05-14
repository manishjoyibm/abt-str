<?php

namespace Abbott\GigyaIM\Test\Unit\Plugin;

class OrderpagecookieTest extends \PHPUnit\Framework\TestCase
{
    public $helperMock;
    /**
     * @var (\Abbott\AwsLambda\Logger\Log & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $loggerMock;
    /**
     * @var (\Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $profile;
    public $helper;
    public $salesinlineMock;
    public $orderinlineMock;
    public function setUp() : void
    {
        $this->helperMock  = $this->getMockBuilder(\Abbott\GigyaIM\Helper\Data::class)->disableOriginalConstructor()->setMethods(['setCookie', 'getCustomCookie'])->getMock();

        $this->loggerMock  = $this->getMockBuilder(\Abbott\AwsLambda\Logger\Log::class)->disableOriginalConstructor()->getMock();

        $this->profile  = $this->getMockBuilder(\Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory::class)->disableOriginalConstructor()->setMethods(['create','addFieldToFilter','getSize'])->getMock();

        $this->helper = new \Abbott\GigyaIM\Plugin\Orderpagecookie($this->helperMock, $this->loggerMock, $this->profile);
    }
    public function testAfterPlace()
    {
        $test['abt_usr'] = '{"customer_id":"12","token":"123456","fname":"Kruti","cgroup":"1234567789","link_hide":{"returns":1},"magento_page":{"orders":1,"subscriptions":1}}';

        $this->helperMock->method('getCustomCookie')->will($this->returnValue($test['abt_usr']));

        $customer_id = 234567;
        $this->salesinlineMock = $this->getMockBuilder(\Magento\Sales\Api\OrderManagementInterface::class)->disableOriginalConstructor()->setMethods(['getIncrementId','getCustomerId','cancel','getCommentsList','addComment','notify','getStatus','hold','unHold','place'])->getMock();
        $this->orderinlineMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)->disableOriginalConstructor()->setMethods(['getAdjustmentNegative','getAdjustmentPositive','getAppliedRuleIds','getBaseAdjustmentNegative','getBaseAdjustmentPositive','getBaseCurrencyCode','getBaseDiscountAmount','getBaseDiscountCanceled','getBaseDiscountInvoiced','getBaseDiscountRefunded','getBaseGrandTotal','getBaseDiscountTaxCompensationAmount','getBaseDiscountTaxCompensationInvoiced','getBaseDiscountTaxCompensationRefunded','getBaseShippingAmount','getBaseShippingCanceled','getBaseShippingDiscountAmount','getBaseShippingDiscountTaxCompensationAmnt','getBaseShippingInclTax','getBaseShippingInvoiced','getBaseShippingRefunded','getBaseShippingTaxAmount','getBaseShippingTaxRefunded','getBaseSubtotal','getBaseSubtotalCanceled','getBaseSubtotalInclTax','getBaseSubtotalInvoiced','getBaseSubtotalRefunded','getBaseTaxAmount','getBaseTaxCanceled','getBaseTaxInvoiced','getBaseTaxRefunded','getBaseTotalCanceled','getBaseTotalDue','getBaseTotalInvoiced','getBaseTotalInvoicedCost','getBaseTotalOfflineRefunded','getBaseTotalOnlineRefunded','getBaseTotalPaid','getBaseTotalQtyOrdered','getBaseTotalRefunded','getBaseToGlobalRate','getBaseToOrderRate','getBillingAddressId','getCanShipPartially','getCanShipPartiallyItem','getCouponCode','getCreatedAt','setCreatedAt','getCustomerDob','getCustomerEmail','getCustomerFirstname','getCustomerGender','getCustomerGroupId','getCustomerId','getCustomerIsGuest','getCustomerLastname','getCustomerMiddlename','getCustomerNote','getCustomerNoteNotify','getCustomerPrefix','getCustomerSuffix','getCustomerTaxvat','getDiscountAmount','getDiscountCanceled','getDiscountDescription','getDiscountInvoiced','getDiscountRefunded','getEditIncrement','getEmailSent','getEntityId','setEntityId','getExtCustomerId','getExtOrderId','getForcedShipmentWithInvoice','getGlobalCurrencyCode','getGrandTotal','getDiscountTaxCompensationAmount','getDiscountTaxCompensationInvoiced','getDiscountTaxCompensationRefunded','getHoldBeforeState','getHoldBeforeStatus','getIncrementId','getIsVirtual','getOrderCurrencyCode','getOriginalIncrementId','getPaymentAuthorizationAmount','getPaymentAuthExpiration','getProtectCode','getQuoteAddressId','getQuoteId','getRelationChildId','getRelationChildRealId','getRelationParentId','getRelationParentRealId','getRemoteIp','getShippingAmount','getShippingCanceled','getShippingDescription','getShippingDiscountAmount','getShippingDiscountTaxCompensationAmount','getShippingInclTax','getShippingInvoiced','getShippingRefunded','getShippingTaxAmount','getShippingTaxRefunded','getState','getStatus','getStoreCurrencyCode','getStoreId','getStoreName','getStoreToBaseRate','getStoreToOrderRate','getSubtotal','getSubtotalCanceled','getSubtotalInclTax','getSubtotalInvoiced','getSubtotalRefunded','getTaxAmount','getTaxCanceled','getTaxInvoiced','getTaxRefunded','getTotalCanceled','getTotalDue','getTotalInvoiced','getTotalItemCount','getTotalOfflineRefunded','getTotalOnlineRefunded','getTotalPaid','getTotalQtyOrdered','getTotalRefunded','getUpdatedAt','getWeight','getXForwardedFor','getItems','setItems','getBillingAddress','setBillingAddress','getPayment','getStatusHistories','setStatusHistories','setState','setStatus','setCouponCode','setProtectCode','setShippingDescription','setIsVirtual','setStoreId','setCustomerId','setBaseDiscountAmount','setBaseDiscountCanceled','setBaseDiscountInvoiced','setBaseDiscountRefunded','setBaseGrandTotal','setBaseShippingAmount','setBaseShippingCanceled','setBaseShippingInvoiced','setBaseShippingRefunded','setBaseShippingTaxAmount','setBaseShippingTaxRefunded','setBaseSubtotal','setBaseSubtotalCanceled','setBaseSubtotalInvoiced','setBaseSubtotalRefunded','setBaseTaxAmount','setBaseTaxCanceled','setBaseTaxInvoiced','setBaseTaxRefunded','setBaseToGlobalRate','setBaseToOrderRate','setBaseTotalCanceled','setBaseTotalInvoiced','setBaseTotalInvoicedCost','setBaseTotalOfflineRefunded','setBaseTotalOnlineRefunded','setBaseTotalPaid','setBaseTotalQtyOrdered','setBaseTotalRefunded','setDiscountAmount','setDiscountCanceled','setDiscountInvoiced','setDiscountRefunded','setGrandTotal','setShippingAmount','setShippingCanceled','setShippingInvoiced','setShippingRefunded','setShippingTaxAmount','setShippingTaxRefunded','setStoreToBaseRate','setStoreToOrderRate','setSubtotal','setSubtotalCanceled','setSubtotalInvoiced','setSubtotalRefunded','setTaxAmount','setTaxCanceled','setTaxInvoiced','setTaxRefunded','setTotalCanceled','setTotalInvoiced','setTotalOfflineRefunded','setTotalOnlineRefunded','setTotalPaid','setTotalQtyOrdered','setTotalRefunded','setCanShipPartially','setCanShipPartiallyItem','setCustomerIsGuest','setCustomerNoteNotify','setBillingAddressId','setCustomerGroupId','setEditIncrement','setEmailSent','setForcedShipmentWithInvoice','setPaymentAuthExpiration','setQuoteAddressId','setQuoteId','setAdjustmentNegative','setAdjustmentPositive','setBaseAdjustmentNegative','setBaseAdjustmentPositive','setBaseShippingDiscountAmount','setBaseSubtotalInclTax','setBaseTotalDue','setPaymentAuthorizationAmount','setShippingDiscountAmount','setSubtotalInclTax','setTotalDue','setWeight','setCustomerDob','setIncrementId','setAppliedRuleIds','setBaseCurrencyCode','setCustomerEmail','setCustomerFirstname','setCustomerLastname','setCustomerMiddlename','setCustomerPrefix','setCustomerSuffix','setCustomerTaxvat','setDiscountDescription','setExtCustomerId','setExtOrderId','setGlobalCurrencyCode','setHoldBeforeState','setHoldBeforeStatus','setOrderCurrencyCode','setOriginalIncrementId','setRelationChildId','setRelationChildRealId','setRelationParentId','setRelationParentRealId','setRemoteIp','setStoreCurrencyCode','setStoreName','setXForwardedFor','setCustomerNote','setUpdatedAt','setTotalItemCount','setCustomerGender','setDiscountTaxCompensationAmount','setBaseDiscountTaxCompensationAmount','setShippingDiscountTaxCompensationAmount','setBaseShippingDiscountTaxCompensationAmnt','setDiscountTaxCompensationInvoiced','setBaseDiscountTaxCompensationInvoiced','setDiscountTaxCompensationRefunded','setBaseDiscountTaxCompensationRefunded','setShippingInclTax','setBaseShippingInclTax','getExtensionAttributes','setExtensionAttributes','setPayment'])->getMock();

        $this->orderinlineMock->expects($this->any())->method('getIncrementId')->will($this->returnValue($this->orderinlineMock));

        $this->assertEquals($this->orderinlineMock, $this->helper->afterPlace($this->salesinlineMock, $this->orderinlineMock));
    }
}
