<?php

namespace Abbott\ShoppingCart\Model\Resolver;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Configuration\OptionResolver;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class CartSubscription implements ResolverInterface
{
    public $optionRepository;
    public $planRepository;
    /**
     * @var OptionResolver
     */
    public $optionResolver;
    public $subscriptionOptionProcessor;
    public const OPTION_LABEL = 'option_label';
    public const OPTION_VALUE = 'option_value';

    /**
     * Construct
     *
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param PlanRepositoryInterface $planRepository
     * @param OptionResolver $optionResolver
     * @param Processor $subscriptionOptionProcessor
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        PlanRepositoryInterface $planRepository,
        OptionResolver $optionResolver,
        Processor $subscriptionOptionProcessor
    ) {
        $this->optionRepository = $optionRepository;
        $this->planRepository = $planRepository;
        $this->optionResolver = $optionResolver;
        $this->subscriptionOptionProcessor = $subscriptionOptionProcessor;
    }

    /**
     * Resolver
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cartItem = $value['model'];
        $options = $cartItem->getOptionsByCode();
        if (array_key_exists('aw_sarp2_subscription_type', $options)) {
            $subscriptionOption = $this->optionRepository->get($options['aw_sarp2_subscription_type']->getValue());
            $plan = $this->planRepository->get($subscriptionOption->getPlanId());
            $suboptions = $this->subscriptionOptionProcessor->getDetailedOptions(
                $subscriptionOption,
                $plan->getDefinition()
            );
            $optionsub = [];
            $substart[self::OPTION_LABEL] = "Subscription Start Date";
            $substart[self::OPTION_VALUE] = date('Y-m-d');
            $optionsub[] = $substart;
            foreach ($suboptions as $suboption) {
                $subopt = [];
                if ($suboption['type'] == 'billing_cycle') {
                    $subopt[self::OPTION_LABEL] = "Billing Period";
                    $subopt[self::OPTION_VALUE] = $suboption['value'].' at the below rate + tax/shipping (if applicable)';
                } else {
                    $subopt[self::OPTION_LABEL] = $suboption['label']->getText();
                    $subopt[self::OPTION_VALUE] = $suboption['value'].' at the below rate + tax/shipping (if applicable)';
                }
                $optionsub[] = $subopt;
            }
            return $optionsub;
        }
    }
}
