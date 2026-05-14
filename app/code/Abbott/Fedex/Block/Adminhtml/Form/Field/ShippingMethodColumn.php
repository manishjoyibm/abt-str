<?php
namespace Abbott\Fedex\Block\Adminhtml\Form\Field;

use Magento\Fedex\Model\Source\Method;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class ShippingMethodColumn extends Select
{
    /**
     * @var Method
     */
    private Method $method;

    /**
     * ShippingMethodColumn constructor.
     * @param Context $context
     * @param Method $method
     * @param array $data
     */
    public function __construct(
        Context $context,
        Method $method,
        array $data = []
    ) {
        $this->method = $method;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Get Source Options Method
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        return $this->method->toOptionArray();
    }
}
