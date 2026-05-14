<?php

namespace Abbott\WorkdayFeed\Model\InboundFeed\Source;

/**
 * Custom layout source
 */
class CustomLayout extends InboundFeedLayout
{
    /**
     * Method toOptionArray
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return array_merge([['label' => 'Default', 'value' => '']], parent::toOptionArray());
    }
}
