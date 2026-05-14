<?php

namespace Abbott\Checkout\Block\Magento\Customer\Address;

use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Customer\Model\Address\Mapper;
use Magento\Directory\Model\RegionFactory;

class Renderer extends \Magento\Customer\Block\Address\Renderer\DefaultRenderer
{

    protected $regionFactory;
    /**
     * Format type object
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_type;

    /**
     * @var ElementFactory
     */
    protected $elementFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Customer\Api\AddressMetadataInterface
     */
    protected $addressMetadataService;
    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param ElementFactory $elementFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Customer\Api\AddressMetadataInterface $metadataService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        ElementFactory $elementFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Api\AddressMetadataInterface $metadataService,
        Mapper $addressMapper,
        RegionFactory $regionFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->countryFactory = $countryFactory;
        $this->addressMetadataService = $metadataService;
        $this->addressMapper = $addressMapper;
        $this->regionFactory = $regionFactory;
        parent::__construct($context, $elementFactory, $countryFactory, $metadataService, $addressMapper, $data);
    }
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function renderArray($addressAttributes, $format = null)
    {
        switch ($this->_type->getCode()) {
            case 'html':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_HTML;
                break;
            case 'pdf':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_PDF;
                break;
            case 'oneline':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_ONELINE;
                break;
            default:
                $dataFormat = ElementFactory::OUTPUT_FORMAT_TEXT;
                break;
        }

        $attributesMetadata = $this->addressMetadataService->getAllAttributesMetadata();
        $data = [];
        foreach ($attributesMetadata as $attributeMetadata) {
            if (!$attributeMetadata->isVisible()) {
                continue;
            }
            $attributeCode = $attributeMetadata->getAttributeCode();
            if ($attributeCode == 'country_id' && isset($addressAttributes['country_id'])) {
                $data['country'] = $this->countryFactory->create()->loadByCode(
                    $addressAttributes['country_id']
                )->getName();
            } elseif ($attributeCode == 'region' && isset($addressAttributes['region'])) {
                $data['region'] = $this->getRegionCode($addressAttributes['region'], $addressAttributes['country_id']);
            } elseif (isset($addressAttributes[$attributeCode])) {
                $value = $addressAttributes[$attributeCode];
                $dataModel = $this->elementFactory->create($attributeMetadata, $value, 'customer_address');
                $value = $dataModel->outputValue($dataFormat);
                if ($attributeMetadata->getFrontendInput() == 'multiline') {
                    $values = $dataModel->outputValue(ElementFactory::OUTPUT_FORMAT_ARRAY);
                    // explode lines
                    foreach ($values as $k => $v) {
                        $key = sprintf('%s%d', $attributeCode, $k + 1);
                        $data[$key] = $v;
                    }
                }
                $data[$attributeCode] = $value;
            }
        }
        if ($this->_type->getEscapeHtml()) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->escapeHtml($value);
            }
        }
        $format = $format !== null ? $format : $this->getFormatArray($addressAttributes);
        return $this->filterManager->template($format, ['variables' => $data]);
    }

     /**
      * @param string $region
      * @param string $countryId
      * @return string
      */
    public function getRegionCode($region, $countryId)
    {
        $regionCode = $this->regionFactory->create()->loadByName($region, $countryId);
        return $regionCode->getCode();
    }
}
