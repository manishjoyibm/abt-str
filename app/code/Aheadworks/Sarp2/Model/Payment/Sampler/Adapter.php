<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data\PaymentDataObjectFactory;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\PaymentDataConverter;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Amount as InfoAmount;
use \RuntimeException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

/**
 * Class Adapter
 * @package Aheadworks\Sarp2\Model\Payment\Sampler
 */
class Adapter implements SamplerInterface
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * @var string
     */
    private $paymentMethodCode;

    /**
     * @var InfoAmount
     */
    private $infoAmount;

    /**
     * @var string
     */
    private $placeAction;

    /**
     * @var string
     */
    private $revertAction;

    /**
     * @var PaymentDataConverter
     */
    private $paymentDataConverter;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @param PaymentDataConverter $paymentDataConverter
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $paymentMethodCode
     * @param InfoAmount $infoAmount
     * @param EventManagerInterface $eventManager
     * @param CommandPoolInterface|null $commandPool
     * @param string $placeAction
     * @param string $revertAction
     */
    public function __construct(
        PaymentDataConverter $paymentDataConverter,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $paymentMethodCode,
        InfoAmount $infoAmount,
        EventManagerInterface $eventManager,
        CommandPoolInterface $commandPool = null,
        $placeAction = 'authorize',
        $revertAction = 'void'
    ) {
        $this->paymentDataConverter = $paymentDataConverter;
        $this->valueHandlerPool = $valueHandlerPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->paymentMethodCode = $paymentMethodCode;
        $this->commandPool = $commandPool;
        $this->placeAction = $placeAction;
        $this->revertAction = $revertAction;
        $this->infoAmount = $infoAmount;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function importPayment(SamplerInfoInterface $info, array $data)
    {
        $paymentData = $this->paymentDataConverter->convert($data);
        $info->getMethodInstance()
            ->assignData($paymentData)
            ->validate();
        $info->setAmount($this->infoAmount->getAmount());
        return $info;
    }

    /**
     * {@inheritdoc}
     */
    public function place(SamplerInfoInterface $info)
    {
        $this->setStore($info->getStoreId());
        $this->assertIsMethodActive($info)
            ->assertCanUseForCurrency($info, $info->getBaseCurrencyCode())
            ->assertCanPerformAction($this->placeAction)
            ->assertCanPerformAction($this->revertAction);

        $this->eventManager->dispatch(
            'aw_sarp2_sampler_place_command_before_' . $info->getMethod(),
            [
                'payment' => $info,
            ]
        );

        $this->executeCommand($this->placeAction, ['payment' => $info, 'command' => $this->placeAction]);

        $info->setAmountPlaced($info->getAmount());
        $info->setStatus(SamplerInfoInterface::STATUS_PLACED);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function revert(SamplerInfoInterface $info)
    {
        $this->executeCommand($this->revertAction, ['payment' => $info, 'command' => $this->revertAction]);

        $info->setAmountReverted($info->getAmountPlaced());
        $info->setStatus(SamplerInfoInterface::STATUS_RESOLVED);

        return $this;
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)$this->getConfiguredValue('active', $storeId);
    }

    /**
     * Check authorize availability
     *
     * @return bool
     */
    public function canAuthorize()
    {
        return $this->canPerformCommand('authorize');
    }

    /**
     * Check void command availability
     *
     * @return bool
     */
    public function canVoid()
    {
        return $this->canPerformCommand('void');
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return void
     */
    public function setStore($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStore()
    {
        return $this->storeId;
    }

    /**
     * Whether payment command is supported and can be executed
     *
     * @param string $commandCode
     * @return bool
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function canPerformCommand($commandCode)
    {
        return (bool)$this->getConfiguredValue('can_' . $commandCode);
    }

    /**
     * Unifies configured value handling logic
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function getConfiguredValue($field, $storeId = null)
    {
        $handler = $this->valueHandlerPool->get($field);
        $subject = [
            'field' => $field
        ];

        return $handler->handle($subject, $storeId ?: $this->getStore());
    }

    /**
     * Execute command
     *
     * @param string $commandCode
     * @param array $arguments
     * @return \Magento\Payment\Gateway\Command\ResultInterface|null
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    private function executeCommand($commandCode, array $arguments = [])
    {
        $arguments['payment'] = $this->paymentDataObjectFactory->create($arguments['payment']);
        $command = $this->commandPool->get($commandCode);

        return $command->execute($arguments);
    }

    /**
     * Assert is payment method available
     *
     * @param SamplerInfoInterface $info
     * @return $this
     * @throws RuntimeException
     */
    private function assertIsMethodActive(SamplerInfoInterface $info)
    {
        if (!$this->isActive($info->getStoreId())) {
            throw new RuntimeException('Payment method ' . $info->getMethod() . ' isn\'t active.');
        }
        return $this;
    }

    /**
     * Assert is payment method can be used for specified currency
     *
     * @param SamplerInfoInterface $info
     * @param string $currencyCode
     * @return $this
     * @throws RuntimeException
     */
    private function assertCanUseForCurrency(SamplerInfoInterface $info, $currencyCode)
    {
        if (!$info->getMethodInstance()->canUseForCurrency($info->getStoreId())) {
            throw new RuntimeException(
                'Payment method ' . $info->getMethod() . ' cannot used for currency ' . $currencyCode . '.'
            );
        }
        return $this;
    }

    /**
     * Assert is payment action can be performed
     *
     * @param string $action
     * @return $this
     * @throws RuntimeException
     */
    private function assertCanPerformAction($action)
    {
        $methodName = 'can' . ucwords($action);
        $methodInstance = $this;
        if (!method_exists($methodInstance, $methodName)
            || !$methodInstance->$methodName()
        ) {
            throw new RuntimeException('Payment action ' . $action . ' cannot been performed.');
        }
        return $this;
    }
}
