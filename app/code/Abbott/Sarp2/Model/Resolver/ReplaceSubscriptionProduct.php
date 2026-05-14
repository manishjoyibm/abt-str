<?php

declare(strict_types = 1);

namespace Abbott\Sarp2\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Update product from subscription
 */
class ReplaceSubscriptionProduct implements ResolverInterface {

    public $changeProduct;
    /**
     * @param \Abbott\Sarp2\Model\ChangeProduct $changeProduct
     */
    public function __construct(\Abbott\Sarp2\Model\ChangeProduct $changeProduct) {
        $this->changeProduct = $changeProduct;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
    Field $field, $context, ResolveInfo $info, array $value = null, array $args = null
    ) {
		 /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
		
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        if (!isset($args['input']['profile_id'])) {
            throw new GraphQlInputException(__('"input profile_id" value should be specified'));
        }
        if (!isset($args['input']['old_sku'])) {
            throw new GraphQlInputException(__('"input old_sku" value should be specified'));
        }
        if (!isset($args['input']['sku'])) {
            throw new GraphQlInputException(__('"input sku" value should be specified'));
        }
        if (!isset($args['input']['qty'])) {
            throw new GraphQlInputException(__('"input qty" value should be specified'));
        }

        return $this->changeProduct->execute($args['input']); 
    }

}
