<?php

namespace Abbott\KountCustomFields\Model\Ris\Base\Builder;

use Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface;

class PaymentFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $paymentClass
     * @return PaymentInterface
     * @throws \InvalidArgumentException
     */
    public function create($paymentClass)
    {
        $payment = $this->objectManager->create($paymentClass);
        if (!$payment instanceof PaymentInterface) {
            throw new \InvalidArgumentException(
                get_class($payment) . ' must be an instance of \Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface.'
            );
        }
        return $payment;
    }
}
