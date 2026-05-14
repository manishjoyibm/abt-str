<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;

/**
 * Replaces a "%amasty_cart_id%" value with the current authenticated customer's cart
 */

class ParamOverriderAmastyCartId extends \Magento\Quote\Model\Webapi\ParamOverriderCartId
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        UserContextInterface $userContext,
        CartManagementInterface $cartManagement,
        RequestInterface $request
    ) {
        $this->request = $request;
        parent::__construct($userContext, $cartManagement);
    }

    public function getOverriddenValue()
    {
        try {
            return parent::getOverriddenValue();
        } catch (NoSuchEntityException $e) {
            $jsonContent = json_decode($this->request->getContent(), true);

            if (isset($jsonContent['amastyCartId'])) {
                return $jsonContent['amastyCartId'];
            }

            throw $e;
        }
    }
}
