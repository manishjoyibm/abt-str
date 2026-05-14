<?php
namespace Abbott\PasswordHistory\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Abbott\PasswordHistory\Api\UsedPasswordManagementInterface;

class AccountManagementPlugin
{
    /** @var UsedPasswordManagementInterface */
    private $passwordManagement;

    public function __construct(UsedPasswordManagementInterface $passwordManagement)
    {
        $this->passwordManagement = $passwordManagement;
    }

    /**
     * Validate NEW password before change (customer-initiated change).
     */
    public function beforeChangePassword(
        AccountManagementInterface $subject,
        $email,
        $currentPassword,
        $newPassword
    ) {
        $this->passwordManagement->validatePassword($email, $newPassword);
        return [$email, $currentPassword, $newPassword];
    }

    /**
     * Save NEW password after change.
     * IMPORTANT: include $newPassword in the signature to capture it.
     */
    public function afterChangePassword(
        AccountManagementInterface $subject,
        $result,
        $email,
        $currentPassword,
        $newPassword
    ) {
        // Persist the NEW password to history
        $this->passwordManagement->saveUsedPassword($email, $newPassword);
        return $result;
    }

    /**
     * Validate NEW password before reset (Forgot Password flow).
     */
    public function beforeResetPassword(
        AccountManagementInterface $subject,
        $email,
        $resetToken,
        $newPassword
    ) {
        $this->passwordManagement->validatePassword($email, $newPassword);
        return [$email, $resetToken, $newPassword];
    }

    /**
     * Save NEW password after reset (Forgot Password flow).
     */
    public function afterResetPassword(
        AccountManagementInterface $subject,
        $result,
        $email,
        $resetToken,
        $newPassword
    ) {
        // Persist the NEW password to history
        $this->passwordManagement->saveUsedPassword($email, $newPassword);
        return $result;
    }
}
