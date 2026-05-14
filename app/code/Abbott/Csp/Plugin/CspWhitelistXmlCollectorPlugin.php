<?php

namespace Abbott\Csp\Plugin;

use Magento\Csp\Model\Collector\CspWhitelistXmlCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class CspWhitelistXmlCollectorPlugin
{
    private const XML_PATH_CSP_ENABLED = 'abbott_csp/general/enabled';
    private const XML_PATH_SCRIPT_SRC_ENABLED = 'abbott_csp/script_src/enabled';
    private const XML_PATH_SCRIPT_SRC = 'abbott_csp/script_src/whitelist_entries';
    private const XML_PATH_STYLE_SRC_ENABLED = 'abbott_csp/style_src/enabled';
    private const XML_PATH_STYLE_SRC = 'abbott_csp/style_src/whitelist_entries';
    private const XML_PATH_DEFAULT_SRC_ENABLED = 'abbott_csp/default_src/enabled';
    private const XML_PATH_DEFAULT_SRC = 'abbott_csp/default_src/whitelist_entries';
    private const XML_PATH_BASE_URI_ENABLED = 'abbott_csp/base_uri/enabled';
    private const XML_PATH_BASE_URI = 'abbott_csp/base_uri/whitelist_entries';
    private const XML_PATH_CHILD_SRC_ENABLED = 'abbott_csp/child_src/enabled';
    private const XML_PATH_CHILD_SRC = 'abbott_csp/child_src/whitelist_entries';
    private const XML_PATH_CONNECT_SRC_ENABLED = 'abbott_csp/connect_src/enabled';
    private const XML_PATH_CONNECT_SRC = 'abbott_csp/connect_src/whitelist_entries';
    private const XML_PATH_FONT_SRC_ENABLED = 'abbott_csp/font_src/enabled';
    private const XML_PATH_FONT_SRC = 'abbott_csp/font_src/whitelist_entries';
    private const XML_PATH_FORM_ACTION_ENABLED = 'abbott_csp/form_action/enabled';
    private const XML_PATH_FORM_ACTION = 'abbott_csp/form_action/whitelist_entries';
    private const XML_PATH_FRAME_ANCESTORS_ENABLED = 'abbott_csp/frame_ancestors/enabled';
    private const XML_PATH_FRAME_ANCESTORS = 'abbott_csp/frame_ancestors/whitelist_entries';
    private const XML_PATH_FRAME_SRC_ENABLED = 'abbott_csp/frame_src/enabled';
    private const XML_PATH_FRAME_SRC = 'abbott_csp/frame_src/whitelist_entries';
    private const XML_PATH_IMAGE_SRC_ENABLED = 'abbott_csp/image_src/enabled';
    private const XML_PATH_IMAGE_SRC = 'abbott_csp/image_src/whitelist_entries';
    private const XML_PATH_MANIFEST_SRC_ENABLED = 'abbott_csp/manifest_src/enabled';
    private const XML_PATH_MANIFEST_SRC = 'abbott_csp/manifest_src/whitelist_entries';
    private const XML_PATH_MEDIA_SRC_ENABLED = 'abbott_csp/media_src/enabled';
    private const XML_PATH_MEDIA_SRC = 'abbott_csp/media_src/whitelist_entries';
    private const XML_PATH_OBJECT_SRC_ENABLED = 'abbott_csp/object_src/enabled';
    private const XML_PATH_OBJECT_SRC = 'abbott_csp/object_src/whitelist_entries';
    private const XML_PATH_CRITICAL_SECURITY_OVERRIDE_ENABLED = 'abbott_csp/critical_security_overrides/enabled';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     */
    public function __construct(
        public ScopeConfigInterface $scopeConfig,
        public LoggerInterface      $logger,
        public SerializerInterface  $serializer
    ) {
    }

    /**
     * After Collect Function
     *
     * @param CspWhitelistXmlCollector $subject
     * @param array $result
     * @return array
     */
    public function afterCollect(
        CspWhitelistXmlCollector $subject,
        array $result
    ): array {
        if (!$this->getConfigValue(self::XML_PATH_CSP_ENABLED)) {
            return $result;
        }

        $result = $this->getPoliciesData($result);

        if ($this->getConfigValue(self::XML_PATH_SCRIPT_SRC_ENABLED)) {
            $scriptSrcValue = $this->getConfiguredHashes(self::XML_PATH_SCRIPT_SRC);
            // Add custom dynamic inline script hash values and hosts to csp
            $scriptHashes = $this->getFilteredHashArray($scriptSrcValue);
            $scriptSources = $this->getFilteredHostArray($scriptSrcValue);
            $criticalOverride = $this->getConfigValue(self::XML_PATH_CRITICAL_SECURITY_OVERRIDE_ENABLED);
            $hashesScript = [];
            foreach ($scriptHashes as $hash) {
                $hashesScript[$hash] = 'sha256';
            }
            $result[] = new FetchPolicy(
                'script-src',
                false,
                $scriptSources,
                [],
                false,
                $criticalOverride,
                false,
                [],
                $hashesScript
            );
        }

        if ($this->getConfigValue(self::XML_PATH_STYLE_SRC_ENABLED)) {
            $styleSrcValue = $this->getConfiguredHashes(self::XML_PATH_STYLE_SRC);
            // Add custom dynamic inline style hash values and hosts to csp
            $styleHashes = $this->getFilteredHashArray($styleSrcValue);
            $styleSources = $this->getFilteredHostArray($styleSrcValue);
            $hashesStyle = [];
            foreach ($styleHashes as $hash) {
                $hashesStyle[$hash] = 'sha256';
            }
            $result[] = new FetchPolicy(
                'style-src',
                false,
                $styleSources,
                [],
                false,
                false,
                false,
                [],
                $hashesStyle
            );
        }

        return $result;
    }

    /**
     * Get Configured Hashes
     *
     * @param string $configPath
     * @return array
     */
    private function getConfiguredHashes(string $configPath): array
    {
        $hashes = (string)$this->getConfigValue($configPath);
        if ($hashes) {
            return $this->serializer->unserialize($hashes);
        }
        return [];
    }

    /**
     * Get Config Value
     *
     * @param string $configPath
     * @return mixed
     */
    private function getConfigValue(string $configPath): mixed
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Filtered Host Array
     *
     * @param array $sources
     * @return array
     */
    public function getFilteredHostArray(array $sources): array
    {
        $sources = array_filter($sources, function ($item) {
            return $item['type'] === 'host';
        });

        return array_map(
            function ($code, $title) {
                return $title['url'];
            },
            array_keys($sources),
            $sources
        );
    }

    /**
     * Get Filtered Hash Array
     *
     * @param array $sources
     * @return array
     */
    public function getFilteredHashArray(array $sources): array
    {
        $sources = array_filter($sources, function ($item) {
            return $item['type'] === 'hash';
        });

        return array_map(
            function ($code, $title) {
                return $title['url'];
            },
            array_keys($sources),
            $sources
        );
    }

    /**
     * Get SRI Policies Data
     *
     * @param array $result
     * @return array
     */
    public function getPoliciesData(array $result): array
    {
        $policies = [
            'default-src' => [
                'enabled_path' => self::XML_PATH_DEFAULT_SRC_ENABLED,
                'value_path' => self::XML_PATH_DEFAULT_SRC
            ],
            'base-uri' => [
                'enabled_path' => self::XML_PATH_BASE_URI_ENABLED,
                'value_path' => self::XML_PATH_BASE_URI
            ],
            'child-src' => [
                'enabled_path' => self::XML_PATH_CHILD_SRC_ENABLED,
                'value_path' => self::XML_PATH_DEFAULT_SRC
            ],
            'connect-src' => [
                'enabled_path' => self::XML_PATH_CONNECT_SRC_ENABLED,
                'value_path' => self::XML_PATH_CONNECT_SRC
            ],
            'font-src' => [
                'enabled_path' => self::XML_PATH_FONT_SRC_ENABLED,
                'value_path' => self::XML_PATH_FONT_SRC
            ],
            'form-action' => [
                'enabled_path' => self::XML_PATH_FORM_ACTION_ENABLED,
                'value_path' => self::XML_PATH_FORM_ACTION
            ],
            'frame-ancestors' => [
                'enabled_path' => self::XML_PATH_FRAME_ANCESTORS_ENABLED,
                'value_path' => self::XML_PATH_FRAME_ANCESTORS
            ],
            'frame-src' => [
                'enabled_path' => self::XML_PATH_FRAME_SRC_ENABLED,
                'value_path' => self::XML_PATH_FRAME_SRC
            ],
            'img-src' => [
                'enabled_path' => self::XML_PATH_IMAGE_SRC_ENABLED,
                'value_path' => self::XML_PATH_IMAGE_SRC
            ],
            'manifest-src' => [
                'enabled_path' => self::XML_PATH_MANIFEST_SRC_ENABLED,
                'value_path' => self::XML_PATH_MANIFEST_SRC
            ],
            'media-src' => [
                'enabled_path' => self::XML_PATH_MEDIA_SRC_ENABLED,
                'value_path' => self::XML_PATH_MEDIA_SRC
            ],
            'object-src' => [
                'enabled_path' => self::XML_PATH_OBJECT_SRC_ENABLED,
                'value_path' => self::XML_PATH_OBJECT_SRC
            ],
        ];

        foreach ($policies as $directive => $data) {
            if (!$this->getConfigValue($data['enabled_path'])) {
                continue;
            }
            $sources = $this->getConfiguredHashes($data['value_path']);

            $arr = $this->getFilteredHostArray($sources);
            $result[] = new FetchPolicy($directive, false, $arr);

        }
        return $result;
    }
}
