<?php
declare(strict_types=1);

namespace Abbott\EnterpriseRecaptcha\Plugin;

use Magento\Framework\View\Asset\Minification;

/**
 * Exclude external recaptcha from minification
 */
class ExcludeFromMinification
{
    /**
     * Function aroundGetExcludes
     *
     * @param Minification $subject
     * @param callable $proceed
     * @param string $contentType
     * @return string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType): array
    {
        $result = $proceed($contentType);
        if ($contentType !== 'js') {
            return $result;
        }
        $result[] = 'https://www.google.com/recaptcha/enterprise.js';
        return $result;
    }
}
