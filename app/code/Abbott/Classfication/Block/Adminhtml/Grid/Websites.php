<?php

namespace Abbott\Classfication\Block\Adminhtml\Grid;

use Magento\Framework\DataObject;

class Websites extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public $websiteRepository;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        array $data = array()
    ) {
        $this->websiteRepository = $websiteRepository;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $websiteId = $row->getData($this->getColumn()->getIndex());
        $website = $this->websiteRepository->get($websiteId);
        return $website->getName();
    }
}
