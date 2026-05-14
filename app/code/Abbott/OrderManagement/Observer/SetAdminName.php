<?php

namespace Abbott\OrderManagement\Observer;

class SetAdminName implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->backendAuthSession = $backendAuthSession;
    }

    /**
     * Add admin user name to the order history comment
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $history = $observer->getEvent()->getDataObject();
        $adminUserName = $this->backendAuthSession->getUser()->getUserName();
        if ($adminUserName) {
            $history->setAdminUsername($adminUserName);
        }
    }
}
