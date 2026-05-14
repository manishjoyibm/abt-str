<?php

namespace Abbott\AdditionalAttributes\Plugin;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;

class BuildPlugin
{
    /**
     * Before Build function
     *
     * @param Builder $subject
     * @param string $fieldName
     * @param array $arguments
     * @return array
     */
    public function beforeBuild(Builder $subject, string $fieldName, array $arguments): array
    {
        if ($fieldName == 'categoryList') {
            unset($arguments['filter']['is_active']);
        }
        return [$fieldName, $arguments];
    }
}
