<?php

namespace Abbott\GigyaIM\Plugin;

use Abbott\GigyaIM\Helper\Data as Helper;
use Abbott\AwsLambda\Logger\Log as Logger;
use Magento\Backend\Model\Session;

class CustomerAttributePlugin
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Session
     */
    protected $session;

    /**
     * CoustomerAttributePlugin constructor.
     * @param Helper $helper
     * @param Logger $logger
     * @param Session $session
     */
    public function __construct(
        Helper $helper,
        Logger $logger,
        Session $session
    ) {
        $this->helper  = $helper;
        $this->logger  = $logger;
        $this->session = $session;
    }

    /**
     * Before plugin for getAttributesMeta method.
     * @param \Magento\Customer\Model\AttributeMetadataResolver $subject
     * @param AbstractAttribute $attribute
     * @param Type $entityType
     * @param boolean $allowToShowHiddenAttributes
     *
     * @return array
     */
    public function beforeGetAttributesMeta(
        \Magento\Customer\Model\AttributeMetadataResolver $subject,
        $attribute,
        $entityType,
        $showHiddenAttributes
    ) {
        $customer = $this->session->getData();
        if (
            isset($customer['customer_data']['account']['website_id']) &&
            in_array(
                $attribute->getAttributeCode(),
                ['gigya_uid', 'gigya_deleted_timestamp', 'gigya_username', 'alternate_email']
            )
        ) {
            $websiteId = $customer['customer_data']['account']['website_id'];
            if (
                !$this->helper->isGigyaFieldsEditable($websiteId) ||
                !$this->helper->isGigyaEnabledForWebsite($websiteId)
            ) {
                $attribute->setIsVisible(false);
                $showHiddenAttributes = false;
            }
        } else {
            if (in_array($attribute->getAttributeCode(), ['alternate_email'])) {
                $attribute->setIsVisible(false);
                $showHiddenAttributes = false;
            }
        }
        return [$attribute, $entityType, $showHiddenAttributes];
    }
}
