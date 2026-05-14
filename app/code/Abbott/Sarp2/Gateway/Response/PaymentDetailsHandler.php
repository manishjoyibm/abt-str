<?php

namespace Abbott\Sarp2\Gateway\Response;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Quote\Processor;
use Braintree\Transaction;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Observer\DataAssignObserver;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use \Magento\Quote\Model\QuoteFactory;
use \Magento\Checkout\Model\Session as CheckoutSession;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;



/**
 * Class PaymentDetailsHandler
 * @package PayPal\Braintree\Model\ApplePay
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class PaymentDetailsHandler extends \PayPal\Braintree\Gateway\Response\PaymentDetailsHandler
{
    /**
     * List of additional details
     * @var array
     */
    protected $additionalInformationMapping = [
        self::PROCESSOR_AUTHORIZATION_CODE,
        self::PROCESSOR_RESPONSE_CODE,
        self::PROCESSOR_RESPONSE_TEXT,
    ];

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Processor
     */
    private $quoteProcessor;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /** @var CheckoutSession */
    protected $checkoutSession;

    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * PaymentDetailsHandler constructor.
     * @param SubjectReader $subjectReader
     * @param State $state
     * @param QuoteFactory $quoteFactory
     * @param Processor $quoteProcessor
     * @param CheckoutSession $checkoutSession
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param HasSubscriptions $quoteChecker
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        State $state,
        QuoteFactory $quoteFactory,
        Processor $quoteProcessor,
        CheckoutSession $checkoutSession,
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        HasSubscriptions $quoteChecker,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->state = $state;
        $this->quoteFactory = $quoteFactory;
        $this->quoteProcessor = $quoteProcessor;
        $this->checkoutSession = $checkoutSession;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->quoteChecker = $quoteChecker;
        $this->logger = $logger;
    }


    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var Transaction $transaction */
        $transaction = $this->subjectReader->readTransaction($response);
        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        $token = null;
        $payment->setCcTransId($transaction->id);
        $payment->setLastTransId($transaction->id);

        //remove previously set payment nonce.
        $payment->unsAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE);
        foreach ($this->additionalInformationMapping as $item) {
            if (!isset($transaction->$item)) {
                continue;
            }
            $payment->setAdditionalInformation($item, $transaction->$item);
        }

        if($this->quoteChecker->check($this->checkoutSession->getQuote())){
            if($payment->getMethod() == \PayPal\Braintree\Model\GooglePay\Ui\ConfigProvider::METHOD_CODE){
                $token = $transaction->googlePayCardDetails->token;
            }

            if($payment->getMethod() == \PayPal\Braintree\Model\ApplePay\Ui\ConfigProvider::METHOD_CODE){
                $token = $transaction->applePayCardDetails->token;
            }
            $sarpToken = $this->createProfile($payment,$token);
            $payment->setAdditionalInformation('aw_sarp_payment_token_id', $sarpToken->getTokenId());

        }

        $this->setTransactionSource($payment);
    }

    /**
     * When within admin area; assume MOTO transactionSource
     * @param OrderPaymentInterface $payment
     * @throws LocalizedException
     * @throws LocalizedException
     */
    public function setTransactionSource(OrderPaymentInterface $payment)
    {
        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            $payment->setAdditionalInformation(self::TRANSACTION_SOURCE, 'MOTO');
        }
    }

    /**
     * @param $payment
     * @param $token
     * @return PaymentTokenInterface
     */
    public function createProfile($payment,$token){
        try{
            $quote = $this->checkoutSession->getQuote();
            $profileData = $this->quoteProcessor->createProfiles($quote);

            /** @var PaymentTokenInterface $paymentToken */
            $paymentToken = $this->paymentTokenFactory->create();
            $paymentToken->setPaymentMethod($payment->getMethod())
                ->setType('account')
                ->setTokenValue($token)
                ->setIsActive(true);

            $this->paymentTokenRepository->save($paymentToken);
            return $paymentToken;
        }
        catch (\Exception $e){
            $this->logger->critical($e->getMessage());
        }
    }
}
