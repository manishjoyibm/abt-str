<?php
namespace Abbott\CustomerTwoFactorAuth\Block\Customer;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Abbott\CustomerTwoFactorAuth\Helper\Data;

class AuthNav extends Current implements SortLinkInterface
{
    /**
     * @var \Abbott\CustomerTwoFactorAuth\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Response append
     *
     * @return string|null
     */
    protected function _toHtml()
    {
        $responseHtml = null; //  need to return at-least null
        if ($this->helper->isLoggedIn() && $this->helper->isModuleEnabled()) {
                $responseHtml = parent::_toHtml();
        }
        return $responseHtml;
    }

    /**
     * Get sort order
     *
     * @return array|int|mixed|null
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
