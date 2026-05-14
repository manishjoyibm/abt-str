<?php

declare(strict_types=1);

namespace Abbott\CreditCards\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Psr\Log\LoggerInterface;

class MyCreditCardObserver implements ObserverInterface
{
    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var IsCaptchaEnabledInterface
     */
    private $isCaptchaEnabled;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Curl
     */
    protected $curl;
    private const VERIFY_SERVER = 'https://www.google.com/recaptcha/api/siteverify';
    private const XML_PATH_PRIVATE_KEY = 'recaptcha_frontend/type_recaptcha_v3/private_key';
    private const XML_PATH_VALIDATION_FAILURE = 'recaptcha_frontend/failure_messages/validation_failure_message';
    private const XML_PATH_SCORE_THRESHOLD = 'recaptcha_frontend/type_recaptcha_v3/score_threshold';

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * construct function
     *
     * @param Curl $curl
     * @param Data $helper
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ActionFlag $actionFlag
     * @param IsCaptchaEnabledInterface $isCaptchaEnabled
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        Data $helper,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        ActionFlag $actionFlag,
        IsCaptchaEnabledInterface $isCaptchaEnabled,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->actionFlag = $actionFlag;
        $this->isCaptchaEnabled = $isCaptchaEnabled;
        $this->logger = $logger;
    }

    /**
     * Validates reCaptcha response.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $key = 'mycreditcardform';
        if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key)) {
            /** @var Action $controller */
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $requestDecoded = $this->helper->jsonDecode($controller->getRequest()->getContent());
            $request = $request->setParams($requestDecoded);

            $validationResult = false;
            try {
                $customResponse = $request->getParam("g-recaptcha-response");
                $secret = trim((string)$this->scopeConfig->getValue(self::XML_PATH_PRIVATE_KEY));
                $validationResponse = $this->validateCaptcha($secret, $customResponse);
                if (($validationResponse['success']) && ($validationResponse['score'] >
                (float)$this->scopeConfig->getValue(
                    self::XML_PATH_SCORE_THRESHOLD
                ))) {
                    $validationResult = $validationResponse['success'];
                }
            } catch (InputException $e) {
                $this->logger->error($e);
            }
            if (false === $validationResult) {
                $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                $errorMessage = $this->scopeConfig->getValue(
                    self::XML_PATH_VALIDATION_FAILURE
                );
                $this->messageManager->addErrorMessage($errorMessage);
                $this->logger->error(
                    __(
                        'reCAPTCHA validation error'
                    )
                );
            }
        }
    }

    /**
     * Validate captcha
     *
     * @param string $secret
     * @param string $response
     * @return array $result
     * */

    public function validateCaptcha(string $secret, string $response): array
    {
        $url = self::VERIFY_SERVER;
        $result = [];
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->post($url, ['secret' => $secret, 'response' => $response]);
        $curlResponse = $this->curl->getBody();
        $curlResponse = $this->helper->jsonDecode($curlResponse);
        $result['success'] = $curlResponse['success'];
        if ($result['success']) {
            $result['score'] = $curlResponse['score'];
        }
        return $result;
    }
}
