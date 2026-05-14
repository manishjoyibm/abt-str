<?php

namespace Abbott\DisableApi\Model\Resolver;

use Abbott\DisableApi\Helper\Data;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class CreateCustomer
{

    /**
     * @var Data
     */
    private $helper;

    public const XML_PATH_CREATE_CUSTOMER = "abbott_disable_gq_api/grpahql_api/enable_create_customer";

    /**
     * Construct function
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {

        $this->helper = $helper;
    }

    /**
     * Before Resolve function
     *
     * @param \Magento\CustomerGraphQl\Model\Resolver\CreateCustomer $subject
     * @return void
     * @throws GraphQlAuthorizationException
     */
    public function beforeResolve(\Magento\CustomerGraphQl\Model\Resolver\CreateCustomer $subject)
    {

        if ($this->helper->checkApiStatus(self::XML_PATH_CREATE_CUSTOMER)) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
    }
}
