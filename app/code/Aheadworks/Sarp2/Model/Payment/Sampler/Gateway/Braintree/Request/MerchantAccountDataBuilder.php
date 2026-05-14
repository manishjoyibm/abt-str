<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class MerchantAccountDataBuilder
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Braintree\Request
 */
class MerchantAccountDataBuilder implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(ConfigInterface $config, SubjectReader $subjectReader)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $profile = $paymentDO->getProfile();

        $result = [];
        $merchantAccountId = $this->config->getMerchantAccountId($profile->getStoreId());
        if (!empty($merchantAccountId)) {
            $result['merchantAccountId'] = $merchantAccountId;
        }

        return $result;
    }
}
