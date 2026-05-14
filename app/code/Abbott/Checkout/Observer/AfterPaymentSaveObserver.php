<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Abbott\Checkout\Observer;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

/**
 * Order payment after save observer for storing payment vault record in db
 */
class AfterPaymentSaveObserver extends \Magento\Vault\Observer\AfterPaymentSaveObserver
{
    /**
     * Create payment vault record
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var OrderPaymentInterface $payment */
        $payment = $observer->getDataByKey(\Magento\Vault\Observer\AfterPaymentSaveObserver::PAYMENT_OBJECT_DATA_KEY);
        $paymentData = $payment->getAdditionalInformation();

        if (array_key_exists('is_active_payment_token_enabler', $paymentData) &&
            !empty($paymentData['is_active_payment_token_enabler'])) {
            $extensionAttributes = $payment->getExtensionAttributes();

            $paymentToken = $this->getPaymentToken($extensionAttributes);
            if ($paymentToken === null) {
                return $this;
            }

            if ($paymentToken->getEntityId() !== null) {
                $this->paymentTokenManagement->addLinkToOrderPayment(
                    $paymentToken->getEntityId(),
                    $payment->getEntityId()
                );

                return $this;
            }

            $order = $payment->getOrder();

            $paymentToken->setCustomerId($order->getCustomerId());
            $paymentToken->setIsActive(true);
            $paymentToken->setPaymentMethodCode($payment->getMethod());

            $additionalInformation = $payment->getAdditionalInformation();
            $paymentToken->setIsVisible(
                (bool) (int) ($additionalInformation[VaultConfigProvider::IS_ACTIVE_CODE] ?? 0)
            );

            $paymentToken->setPublicHash($this->generatePublicHash($paymentToken));

            $this->paymentTokenManagement->saveTokenWithPaymentLink($paymentToken, $payment);

            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }

        return $this;
    }
}
