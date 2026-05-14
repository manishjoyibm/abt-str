<?php

namespace Abbott\KountCustomFields\Model\Ris\Base\Builder;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\DataObject;

class Payment implements \Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface
{
    /**
     * @var \Abbott\KountCustomFields\Model\Ris\Base\Builder\PaymentFactory
     */
    protected $paymentBuilderFactory;

    /**
     * @var array
     */
    protected $payments;

    /**
     * @var string
     */
    protected $defaultPayment;

    /**
     * @param \Abbott\KountCustomFields\Model\Ris\Base\Builder\PaymentFactory $paymentBuilderFactory
     * @param string $defaultPayment
     * @param array $payments
     */
    public function __construct(
        \Abbott\KountCustomFields\Model\Ris\Base\Builder\PaymentFactory $paymentBuilderFactory,
        $defaultPayment,
        array $payments
    ) {
        $this->paymentBuilderFactory = $paymentBuilderFactory;
        $this->defaultPayment = $defaultPayment;
        $this->payments = $payments;
    }

    /**
     * @param string $paymentCode
     * @return string
     */
    protected function getClassByCode($paymentCode)
    {
        return isset($this->payments[$paymentCode]) ? $this->payments[$paymentCode] : $this->defaultPayment;
    }

    /**
     * @param DataObject $request
     * @param OrderPaymentInterface $payment
     */
    public function process(DataObject $request, OrderPaymentInterface $payment)
    {
        $paymentBuilderClass = $this->getClassByCode($payment->getMethod());
        $paymentBuilder = $this->paymentBuilderFactory->create($paymentBuilderClass);
        $paymentBuilder->process($request, $payment);
    }
}
