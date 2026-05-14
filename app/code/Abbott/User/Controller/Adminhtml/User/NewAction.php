<?php

namespace Abbott\User\Controller\Adminhtml\User;

use Magento\Framework\App\Action\HttpGetActionInterface;

class NewAction extends \Magento\User\Controller\Adminhtml\User\NewAction implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_User::create_user';
}
