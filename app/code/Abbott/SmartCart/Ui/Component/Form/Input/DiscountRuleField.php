<?php

namespace Abbott\SmartCart\Ui\Component\Form\Input;

use Abbott\SmartCart\Model\SmartCart;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\SalesRule\Model\RuleFactory;

class DiscountRuleField extends \Magento\Ui\Component\Form\Field
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * DiscountRuleField constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param RuleFactory $ruleFactory
     * @param Registry $registry
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RuleFactory $ruleFactory,
        Registry $registry,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->ruleFactory = $ruleFactory;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        /** @var SmartCart $smartCart */
        $smartCart = $this->registry->registry("smartcart");
        if ($ruleId = $smartCart->getDiscountRuleId()) {
            $rule = $this->ruleFactory->create()->load($ruleId);
            if ($rule->getId()) {
                $config['value'] = $rule->getCouponCode();
                $this->setData('config', (array)$config);
            }
        }
        parent::prepare();
    }
}
