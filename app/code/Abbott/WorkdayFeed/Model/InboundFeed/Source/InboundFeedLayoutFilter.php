<?php

namespace Abbott\WorkdayFeed\Model\InboundFeed\Source;

/**
 * Inboundfeed layout filter source
 */
class InboundFeedLayoutFilter extends InboundFeedLayout
{
    /**
     * Method toOptionArray
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return array_merge([['label' => '', 'value' => '']], parent::toOptionArray());
    }
}
