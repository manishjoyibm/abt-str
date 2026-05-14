<?php

namespace Abbott\WorkdayFeed\Model\InboundFeed;

use Magento\Framework\Config\ValidationStateInterface;

class DomValidationState implements ValidationStateInterface
{
    /**
     * Retrieve validation state
     *
     * @return boolean
     */
    public function isValidationRequired(): bool
    {
        return true;
    }
}
