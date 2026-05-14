<?php
namespace Abbott\AbbottReport\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdminSessionLoginSuccessObserver implements ObserverInterface
{
    /**
     * @var AdminLogin
     */
    protected $adminLogin;

    /**
     * @param AdminLogin $adminLogin
     */
    public function __construct(
        \Abbott\AbbottReport\Observer\AdminLogin $adminLogin
    ) {
        $this->adminLogin = $adminLogin;
    }

    /**
     * Log successful admin sign in
     *
     * @param Observer $observer
     * @return void
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $this->adminLogin->logAdminLogin($observer->getUser()->getUsername(), $observer->getUser()->getId());
    }
}
