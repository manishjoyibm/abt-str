<?php

namespace Abbott\DisableApi\Model\Resolver;

use Abbott\DisableApi\Helper\Data;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class IsEmailAvailable
{

    /**
     * @var Data
     */
    private $helper;

    public const XML_PATH_EMAIL_AVAILABLE = "abbott_disable_gq_api/grpahql_api/enable_isemail_available";

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
     * @param \Magento\CustomerGraphQl\Model\Resolver\IsEmailAvailable $subject
     * @return void
     * @throws GraphQlAuthorizationException
     */
    public function beforeResolve(\Magento\CustomerGraphQl\Model\Resolver\IsEmailAvailable $subject)
    {

        if ($this->helper->checkApiStatus(self::XML_PATH_EMAIL_AVAILABLE)) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
    }
}
