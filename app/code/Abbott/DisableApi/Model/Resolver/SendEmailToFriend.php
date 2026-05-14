<?php

namespace Abbott\DisableApi\Model\Resolver;

use Abbott\DisableApi\Helper\Data;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class SendEmailToFriend
{
    /**
     * @var Data
     */
    private $helper;

    public const XML_PATH_DISABLE_SEND_EMAIL = "abbott_disable_gq_api/grpahql_api/enable_sendemailto_friend";

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
     * @param \Magento\SendFriendGraphQl\Model\Resolver\SendEmailToFriend $subject
     * @return void
     * @throws GraphQlAuthorizationException
     */
    public function beforeResolve(\Magento\SendFriendGraphQl\Model\Resolver\SendEmailToFriend $subject)
    {
        if ($this->helper->checkApiStatus(self::XML_PATH_DISABLE_SEND_EMAIL)) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
    }
}
