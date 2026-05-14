<?php


namespace Abbott\Targetbase\Api\Data;

interface TargetbaseOrderInterface
{

    /**
     * @return mixed
     */
    public function getStoreId();

    /**
     * @param $storeId
     * @return mixed
     */
    public function setStoreId($storeId);

    /**
     * @return mixed
     */
    public function getCustomerId();

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * @return mixed
     */
    public function getOrderType();

    /**
     * @param $orderType
     * @return mixed
     */
    public function setOrderType($orderType);

    /**
     * @return mixed
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return mixed
     */
    public function setOrderId($orderId);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt);

    /**
     * @return mixed
     */
    public function getCouponCode();

    /**
     * @param $couponCode
     * @return mixed
     */
    public function setCouponCode($couponCode);

    /**
     * @return mixed
     */
    public function getPaymentMethod();

    /**
     * @param $method
     * @return mixed
     */
    public function setPaymentMethod($method);

    /**
     * @return mixed
     */
    public function getGrandTotal();

    /**
     * @param $grandTotal
     * @return mixed
     */
    public function setGrandTotal($grandTotal);

    /**
     * @return mixed
     */
    public function getProductBrand();

    /**
     * @param $productBrand
     * @return mixed
     */
    public function setProductBrand($productBrand);

    /**
     * @return mixed
     */
    public function getProductSku();

    /**
     * @param $sku
     * @return mixed
     */
    public function setProductSku($sku);

    /**
     * @return mixed
     */
    public function getProductName();

    /**
     * @param $name
     * @return mixed
     */
    public function setProductName($name);

    /**
     * @return mixed
     */
    public function getProductQtyOrdered();

    /**
     * @param $qty
     * @return mixed
     */
    public function setProductQtyOrdered($qty);

    /**
     * @return mixed
     */
    public function getProductPrice();

    /**
     * @param $price
     * @return mixed
     */
    public function setProductPrice($price);

    public function getTaxAmount();

    public function setTaxAmount($taxAmount);

    public function getShippingAmount();

    public function setShippingAmount($shippingAmount);
}
