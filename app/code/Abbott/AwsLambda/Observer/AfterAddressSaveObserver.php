<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Abbott\AwsLambda\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Customer Observer Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterAddressSaveObserver implements ObserverInterface
{
    public $request;
    const LOG_MESSAGE = "Aws-Lambda-Api - profile info :";
    const DATE_FORMAT = 'd-m-Y H:i:s';

    /**
     * @var Abbott\AwsLambda\Helper\Data
     */
    protected $helper;

    /**
     * @var Abbott\AwsLambda\Logger\Log
     */
    protected $log;

    /**
     * Constructor
     *
     * @param \Abbott\AwsLambda\Helper\Data $helper
     * @param \Abbott\AwsLambda\Logger\Log  $log
     */
    public function __construct(
        \Abbott\AwsLambda\Helper\Data $helper,
        \Abbott\AwsLambda\Logger\Log $log,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->log = $log;
        $this->request =$request;
    }

    /**
     * Address after save event handler
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $customerAddress = $observer->getCustomerAddress();
            $customer = $customerAddress->getCustomer();
            $this->helper->setStoreId($customer->getStoreId());
            $isNew = false;
            if ($this->request->getParam('is_new')) {
                $isNew = $this->request->getParam('is_new');
            }
            $addressId = 0;
            if ($this->request->getParam('id')) {
                $addressId = $this->request->getParam('id');
            }
            $this->log->writeLog('Customer get default shipping:'.$customer->getDefaultShipping());
            $this->log->writeLog('Customer get default billing:'.$this->_isDefaultBilling($customer));
            $this->log->writeLog('Customer get user type:'.$customer->getUserType());

            if (is_null($this->_isDefaultBilling($customer)) && $customer->getDefaultShipping() && $customer->getUserType() ==  \Abbott\Strongmoms\Helper\Data::IS_SIMILAC_SSM) {
                $billingAddrId = ($addressId) ? $addressId : $customer->getDefaultShipping();
                $this->log->writeLog('customer billing address id:'.$billingAddrId);
                $customer->setEntityId($customer->getEntityId());
                $customer->setDefaultBilling($billingAddrId);
                $customer->save();
            }
            if ($this->helper->enabled() && $addressId && !$isNew && $this->_isDefaultShipping($customerAddress)) {
                $this->log->writeLog(self::LOG_MESSAGE.'Started');
                $street = explode("\n", $customerAddress['street']);
                $street2 = (count($street) > 1) ? $street[1] : null;
                $params = '{
                    "userInfo": {
                        "firstName": "'.$customer["firstname"].'",
                        "lastName": "'.$customer["lastname"].'"
                    },
                    "addresses": [
                        {
                            "zipCode": "'.$customerAddress["postcode"].'",
                            "city": "'.$customerAddress["city"].'",
                            "state": "'.$customerAddress["region_code"].'",
                            "country": "'.$customerAddress["country_id"].'",
                            "lineOne": "'.$street[0].'",
                            "lineTwo": "'.$street2.'"
                        }
                    ],
                    "category": "profileInfo"
                }';
                $url = $this->helper->getPostUrl();
                $this->log->writeLog(
                    self::LOG_MESSAGE.print_r(
                        ["Url" => $url,
                            "Params" => $params,
                            "log_time"=>date(self::DATE_FORMAT)
                        ],
                        true
                    )
                );
                $gigyaUid = $this->helper->getGigyaUid($customer->getId());
                $response = $this->helper->postData($url, $params, $gigyaUid);
                $this->log->writeLog(self::LOG_MESSAGE.print_r(
                    [
                    "Response" => $response,
                    "log_time"=>date(self::DATE_FORMAT)
                    ],
                    true
                ));
            }
            return $this;
        } catch (\Exception $e) {
            $this->log->writeLog(self::LOG_MESSAGE.print_r(
                [
                    "Error" => $e->getMessage(),
                    "log_time"=>date(self::DATE_FORMAT)
                ],
                true
                ));
        }
    }

    /**
     * Check whether specified shipping address is default for its customer
     *
     * @param  Address $address
     * @return bool
     */
    protected function _isDefaultShipping($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultShipping()
        || $address->getIsPrimaryShipping()
        || $address->getIsDefaultShipping();
    }

     /**
      * Check whether default billing address for customer is set or not
      *
      * @param  Customer $customer
      * @return bool
      */
    protected function _isDefaultBilling($customer)
    {
        return $customer->getDefaultBilling();
    }
}
