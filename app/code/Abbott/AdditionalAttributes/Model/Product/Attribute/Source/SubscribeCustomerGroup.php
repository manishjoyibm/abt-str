<?php


namespace Abbott\AdditionalAttributes\Model\Product\Attribute\Source;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class SubscribeCustomerGroup extends AbstractSource implements \Magento\Framework\Option\ArrayInterface
{

    protected $customerGroup;

    protected $options;

    public function __construct(Collection $customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->customerGroup->toOptionArray();
        }
        return $this->options;
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
