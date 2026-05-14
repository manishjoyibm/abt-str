<?php
namespace Abbott\MetabolicOrdering\Block\Adminhtml\Edit;

class GenericButton
{
    public $registry;
    public const CURRENT_METABOLIC_ID = 'current_entity_id';

    /**
     * @var urlBuilder
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Return the metabolic Id.
     *
     * @return int|null
     */
    public function getMetabolicId()
    {
        return $this->registry->registry(self::CURRENT_METABOLIC_ID);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
