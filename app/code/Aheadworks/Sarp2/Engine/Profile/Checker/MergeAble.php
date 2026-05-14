<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Profile\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Config;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Profile\ShippingMethod;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleSet;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Specification;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class MergeAble
 * @package Aheadworks\Sarp2\Engine\Profile\Checker
 */
class MergeAble
{
    /**
     * @var RuleSet
     */
    private $ruleSet;

    /**
     * @var ShippingMethod
     */
    private $shippingMethodResolver;

    /**
     * @var Config
     */
    private $engineConfig;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param RuleSet $ruleSet
     * @param ShippingMethod $shippingMethodResolver
     * @param Config $engineConfig
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        RuleSet $ruleSet,
        ShippingMethod $shippingMethodResolver,
        Config $engineConfig,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->ruleSet = $ruleSet;
        $this->shippingMethodResolver = $shippingMethodResolver;
        $this->engineConfig = $engineConfig;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Check if profiles can be merged
     *
     * @param ProfileInterface $profile1
     * @param ProfileInterface $profile2
     * @return bool
     */
    public function check(ProfileInterface $profile1, ProfileInterface $profile2)
    {
        if ($profile1->getCustomerIsGuest() || $profile2->getCustomerIsGuest()) {
            return false;
        }

        if (!$this->checkEntitiesData($profile1, $profile2, ProfileInterface::class)
            || !$this->checkEntitiesData(
                $profile1->getShippingAddress(),
                $profile2->getShippingAddress(),
                ProfileAddressInterface::class
            )
        ) {
            return false;
        }

        $shippingMethod = $this->shippingMethodResolver->getResolvedValue(
            [$profile1, $profile2],
            ProfileInterface::CHECKOUT_SHIPPING_METHOD
        );
        if (!$shippingMethod) {
            return false;
        }

        if (!$this->engineConfig->isVirtualProfilesBundleEnabled()
            && ($profile1->getIsVirtual() || $profile2->getIsVirtual())
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if entities merge able according to data merge specification
     *
     * @param object $firstEntity
     * @param object $secondEntity
     * @param string $entityType
     * @return bool
     */
    private function checkEntitiesData($firstEntity, $secondEntity, $entityType)
    {
        $data1 = $this->dataObjectProcessor->buildOutputDataArray($firstEntity, $entityType);
        $data2 = $this->dataObjectProcessor->buildOutputDataArray($secondEntity, $entityType);

        $fields = $this->ruleSet->getFields($entityType, Specification::TYPE_SAME);
        foreach ($fields as $field) {
            if (isset($data1[$field]) && isset($data2[$field])) {
                if ($data1[$field] != $data2[$field]) {
                    return false;
                }
            }
        }
        return true;
    }
}
