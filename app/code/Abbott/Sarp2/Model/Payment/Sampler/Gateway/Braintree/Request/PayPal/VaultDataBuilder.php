<?php

namespace Abbott\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Request\PayPal;

use Magento\Payment\Gateway\Request\BuilderInterface;

class VaultDataBuilder implements BuilderInterface
{
    /**
     * Additional options in request to gateway
     */
    const OPTIONS = 'options';

    /**
     * The option that determines whether the payment method associated with
     * the successful transaction should be stored in the Vault.
     */
    const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

    const SKIP_ADVANCED_FRAUD_CHECKING = 'skipAdvancedFraudChecking';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            self::OPTIONS => [
                self::SKIP_ADVANCED_FRAUD_CHECKING => true,
                self::STORE_IN_VAULT_ON_SUCCESS => true
            ]
        ];
    }
}
