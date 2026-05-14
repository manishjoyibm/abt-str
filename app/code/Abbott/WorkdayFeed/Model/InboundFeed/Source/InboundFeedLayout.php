<?php

namespace Abbott\WorkdayFeed\Model\InboundFeed\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

class InboundFeedLayout implements OptionSourceInterface
{
    /**
     * @var BuilderInterface
     */
    protected BuilderInterface $inboundfeedLayoutBuilder;

    /**
     * @var array
     */
    protected array $options;

    /**
     * Constructor
     *
     * @param BuilderInterface $inboundfeedLayoutBuilder
     */
    public function __construct(BuilderInterface $inboundfeedLayoutBuilder)
    {
        $this->inboundfeedLayoutBuilder = $inboundfeedLayoutBuilder;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $configOptions = $this->inboundfeedLayoutBuilder->getPageLayoutsConfig()->getOptions();
        $optionsArray = [];
        foreach ($configOptions as $key => $value) {
            $optionsArray[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        $this->options = $optionsArray;

        return $this->options;
    }
}
