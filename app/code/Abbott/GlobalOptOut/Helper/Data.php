<?php

namespace Abbott\GlobalOptOut\Helper;

use Abbott\GlobalOptOut\Model\ResourceModel\Globalopt\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    public const GLOBAL_OPT_SFTP_USERNAME = 'global_optout_settings/global_optout_sftp/username';
    public const GLOBAL_OPT_SFTP_HOST = 'global_optout_settings/global_optout_sftp/host';
    public const GLOBAL_OPT_SFTP_PORT = 'global_optout_settings/global_optout_sftp/port';
    public const GLOBAL_OPT_SFTP_PASSWORD = 'global_optout_settings/global_optout_sftp/password';
    public const IS_ENABLED_GLOBAL_OPT = 'global_optout_settings/global_optout_crons/glopt_enabled';

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;
    /**
     * @var Collection
     */
    private Collection $collection;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param Collection $collection
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Collection $collection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->collection = $collection;
    }

    /**
     * Check if feature Enable
     *
     * @return int
     */
    public function isEnabled(): int
    {
        return $this->scopeConfig->getValue(self::IS_ENABLED_GLOBAL_OPT);
    }

    /**
     * Get Host detail
     *
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->scopeConfig->getValue(self::GLOBAL_OPT_SFTP_HOST);
    }

    /**
     * Get port number
     *
     * @return int|string|null
     */
    public function getPort(): int|string|null
    {
        return (int)$this->scopeConfig->getValue(self::GLOBAL_OPT_SFTP_PORT);
    }

    /**
     * Get UserName
     *
     * @return string|null
     */
    public function getUserName(): ?string
    {
        return $this->scopeConfig->getValue(self::GLOBAL_OPT_SFTP_USERNAME);
    }

    /**
     * Get password details
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::GLOBAL_OPT_SFTP_PASSWORD));
    }

    /**
     * Filter Opt Out Emails
     *
     * @return array
     */
    public function filterOptOutEmails(): array
    {
        $optOutCollection =  $this->collection;
        $optOutEmail = [];
        $filterEmails = ["0212SARAH@GMAIL.COM", "ANSLEYLIBRARIAN@GMAIL.COM"];
        if ($optOutCollection->getSize()) {
            foreach ($optOutCollection as $data) {
                $optOutEmail[] = $data->getEmail();
            }
        }
        return array_diff($optOutEmail, $filterEmails);
    }
}
