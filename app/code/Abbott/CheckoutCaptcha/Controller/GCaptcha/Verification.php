<?php


namespace Abbott\CheckoutCaptcha\Controller\GCaptcha;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\ReCaptchaValidationApi\Api\ValidatorInterface;
use Magento\ReCaptchaValidationApi\Api\Data\ValidationConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Abbott\CheckoutCaptcha\Helper\Config;

class Verification extends Action
{

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    public $context;
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ValidationConfigInterface
     */
    private $validationConfig;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Config
     */
    protected $scopeConfig;

    /**
     * Construct function
     *
     * @param Context $context
     * @param ValidatorInterface $validator
     * @param ValidationConfigInterface $validationConfig
     * @param JsonFactory $resultJsonFactory
     * @param Config $scopeConfig
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        ValidatorInterface $validator,
        ValidationConfigInterface  $validationConfig,
        JsonFactory $resultJsonFactory,
        Config $scopeConfig,
        RemoteAddress $remoteAddress
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->validator = $validator;
        $this->validationConfig = $validationConfig;
        $this->context = $context;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if ($this->scopeConfig->isEnabledFrontendCreditCard()) {
            if (empty($this->getRequest()->getParam('g-response'))) {
                return $resultJson->setData(['response' => false]);
            }
            $response = $this->validator->isValid(
                $this->getRequest()->getParam('g-response'),
                $this->validationConfig
            );
            return $resultJson->setData(['response' => $response]);
        } else {
            return $resultJson->setData(['response' => false]);
        }
    }
}
