<?php

namespace Abbott\Checkout\Controller\Payment;

use PayPal\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Webapi\Exception;
use Psr\Log\LoggerInterface;


class GetNonce extends Action
{
    public $scopeConfig;
    const FAIL_MSG = 'braintree_custom/braintree_msg/token_failure_msg';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var GetPaymentNonceCommand
     */
    private $command;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param SessionManagerInterface $session
     * @param GetPaymentNonceCommand $command
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        SessionManagerInterface $session,
        GetPaymentNonceCommand $command,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->session = $session;
        $this->command = $command;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $publicHash = $this->getRequest()->getParam('public_hash');
            $customerId = $this->session->getCustomerId();
            $result = $this->command->execute(['public_hash' => $publicHash, 'customer_id' => $customerId])->get();
            $response->setData(['paymentMethodNonce' => $result['paymentMethodNonce']]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->processBadRequest($response);
        }

        return $response;
    }

    /**
     * Return response for bad request
     *
     * @param ResultInterface $response
     * @return ResultInterface
     */
    private function processBadRequest(ResultInterface $response): ResultInterface
    {
        $response->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        $response->setData(['message' => $this->scopeConfig->getValue(self::FAIL_MSG)]);
        return $response;
    }
}
