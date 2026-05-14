<?php
/**
 * filter options for is_progressive
 */

namespace Abbott\Sarp2\Model\Profile\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsProgressive
 * @package Abbott\Sarp2\Model\Profile\Source
 */
class Isprogressive implements OptionSourceInterface
{
    /**
     * Yes
     */
    const YES = 1;

    /**
     * No
     */
    const NO = 0;

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::YES,
                    'label' => __('Yes')
                ],
                [
                    'value' => self::NO,
                    'label' => __('No')
                ],
                [
                    'value' => '',
                    'label' => __('No')
                ]
            ];
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $optionItem) {
            $options[$optionItem['value']] = $optionItem['label'];
        }
        return $options;
    }
}
