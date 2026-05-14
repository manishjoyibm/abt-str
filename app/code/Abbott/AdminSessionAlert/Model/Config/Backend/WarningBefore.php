<?php
namespace Abbott\AdminSessionAlert\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class WarningBefore extends Value
{
    public function beforeSave()
    {
        $warningBefore = (int) $this->getValue();
        $sessionLifeTime = (int) $this->_config->getValue('admin/security/session_lifetime',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        if($warningBefore >= $sessionLifeTime){
            throw new LocalizedException(__("Session popup time must be less than admin session time."));
        }
        return parent::beforeSave();
    }
}
