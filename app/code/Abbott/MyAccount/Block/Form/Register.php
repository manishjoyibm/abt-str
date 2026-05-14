<?php

namespace Abbott\MyAccount\Block\Form;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\Config;
use Abbott\MyAccount\Helper\Data;

/**
 * Customer register form block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Register extends \Magento\Customer\Block\Form\Register
{

    /**
     * @var \Abbott\MyAccount\Helper\Data
     */
    protected $helper;

    /**
     * Construct function
     *
     * @param Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param Manager $moduleManager
     * @param Session $customerSession
     * @param Url $customerUrl
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        Manager $moduleManager,
        Session $customerSession,
        Url $customerUrl,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
            $data
        );
    }

    /**
     * GetConfigDomainRestriction function
     *
     * @param $key
     * @return ScopeConfigInterface
     */
    public function getConfigDomainRestriction($key)
    {
        return $this->helper->getConfigDomainRestriction($key);
    }
}
