<?php

namespace Abbott\MyAccount\Block\Address;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Address\Config;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Customer address grid
 *
 * @api
 * @since 102.0.1
 */
class Grid extends \Magento\Customer\Block\Address\Grid
{

    /**
     * @var Config
     */
    protected $addressConfig;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * Construct function
     *
     * @param Context $context
     * @param Config $addressConfig
     * @param Mapper $addressMapper
     * @param CurrentCustomer $currentCustomer
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param CountryFactory $countryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $addressConfig,
        Mapper $addressMapper,
        CurrentCustomer $currentCustomer,
        AddressCollectionFactory $addressCollectionFactory,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;

        parent::__construct($context, $currentCustomer, $addressCollectionFactory, $countryFactory, $data);
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param AddressInterface $address
     * @return string
     */
    public function _getAddressHtml($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($this->addressMapper->toFlatArray($address));
    }
}
