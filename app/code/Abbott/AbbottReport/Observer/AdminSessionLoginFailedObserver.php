<?php
namespace Abbott\AbbottReport\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Event\ObserverInterface;

class AdminSessionLoginFailedObserver implements ObserverInterface
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
     * Log failure of sign in
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $eventModel = $this->adminLogin->logAdminLogin($observer->getUserName());

        if (class_exists(\Magento\User\Model\Backend\Observer::class, false) && $eventModel) {
            $exception = $observer->getException();
            if ($exception instanceof UserLockedException) {
                $eventModel->setInfo(__('This user is locked'))->save();
            }
        }
    }
}
