<?php
namespace Abbott\ValidateAdminPassword\Block\Adminhtml\User;

use Magento\Backend\Block\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Validate extends Template
{

    protected $scopeConfig;

    public const XML_ABBOTT_ADMIN_PASSWORD_LENGTH = 'validateadminpassword/adminpassword/password_length';

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * GetPasswordLenght
     *
     * @return interger
     */
    public function getPasswordLength()
    {
        return $this->scopeConfig->getValue(
            self::XML_ABBOTT_ADMIN_PASSWORD_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
