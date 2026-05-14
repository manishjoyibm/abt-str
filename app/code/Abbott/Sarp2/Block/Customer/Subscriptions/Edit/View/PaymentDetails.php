<?php

namespace Abbott\Sarp2\Block\Customer\Subscriptions\Edit\View;

use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\TokenRendererInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;


class PaymentDetails extends \Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails
{

    /**
     * @var PaymentTokenRepositoryInterface
     */
    protected $paymentTokenRepository;

    /**
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    private $storeManager;

    /**
     * @param Context $context
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        PaymentMethodListInterface $paymentMethodList,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $paymentTokenRepository, $data);
        $this->paymentMethodList = $paymentMethodList;
        $this->storeManager = $storeManager;
    }



    /**
     * Render payment token
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    public function renderPaymentToken($paymentToken)
    {
        foreach ($this->getChildNames() as $childName) {
            $childBlock = $this->getChildBlock($childName);
            if ($childBlock instanceof TokenRendererInterface && $childBlock->canRender($paymentToken)) {
                return $childBlock->render($paymentToken);
            } elseif ($paymentToken->getPaymentMethod() != "braintree_paypal") {
                return $this->getPaymentMethodTitle($paymentToken);
            }
        }

        return '';
    }


    /**
     * Get payment method title
     *
     * @return string
     */
    public function getPaymentMethodTitle($paymentToken)
    {
        $methods = $this->paymentMethodList->getList($this->storeManager->getStore()->getId());

        foreach ($methods as $method) {
            if ($method->getCode() == $paymentToken->getPaymentMethod()) {
                return $method->getTitle();
            }
        }
        return '';
    }
}
