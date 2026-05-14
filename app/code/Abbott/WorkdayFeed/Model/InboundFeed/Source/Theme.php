<?php

namespace Abbott\WorkdayFeed\Model\InboundFeed\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Design\Theme\Label\ListInterface;

class Theme implements OptionSourceInterface
{
    /**
     * @var ListInterface
     */
    protected ListInterface $themeList;

    /**
     * Constructor
     *
     * @param ListInterface $themeList
     */
    public function __construct(ListInterface $themeList)
    {
        $this->themeList = $themeList;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options[] = ['label' => 'Default', 'value' => ''];
        return array_merge($options, $this->themeList->getLabels());
    }
}
