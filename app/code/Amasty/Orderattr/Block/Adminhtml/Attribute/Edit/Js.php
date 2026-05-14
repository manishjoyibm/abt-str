<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Block\Adminhtml\Attribute\Edit;

use Amasty\Base\Model\Serializer;
use Magento\Framework\App\ObjectManager;

class Js extends \Magento\Backend\Block\Template
{
    /**
     * @var \Amasty\Orderattr\Model\Attribute\InputType\InputTypeProvider
     */
    private $inputTypeProvider;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\Orderattr\Model\Attribute\InputType\InputTypeProvider $inputTypeProvider,
        array $data = [],
        Serializer $serializer = null // TODO move to not optional
    ) {
        parent::__construct($context, $data);
        $this->inputTypeProvider = $inputTypeProvider;
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(Serializer::class);
    }

    /**
     * @return \Amasty\Orderattr\Model\Attribute\InputType\InputType[]|array
     */
    public function getAttributeInputTypes()
    {
        return $this->inputTypeProvider->getList();
    }

    /**
     * @return array
     */
    public function getAttributeInputTypesWithOptions()
    {
        return $this->inputTypeProvider->getInputTypesWithOptions();
    }

    /**
     * @param mixed $row
     *
     * @return string
     */
    public function encode($row)
    {
        return $this->serializer->serialize($row);
    }
}
