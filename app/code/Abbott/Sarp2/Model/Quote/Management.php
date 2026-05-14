<?php

namespace Abbott\Sarp2\Model\Quote;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerManagement;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Aheadworks\Sarp2\Model\Quote\Processor;
use Aheadworks\Sarp2\PaymentData\AdapterPool;
use Aheadworks\Sarp2\PaymentData\Payment;
use Aheadworks\Sarp2\PaymentData\PaymentFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment as QuotePayment;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Abbott\Sarp2\Plugin\Observer\PaymentMethodAvailabilityObserverPlugin;
use Abbott\Hartehanks\Model\HartehankPlaceOrderSync;

/**
 * Class Management
 * @package Abbott\Sarp2\Model\Quote
 */
class Management extends  \Aheadworks\Sarp2\Model\Quote\Management{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var Processor
     */
    private $quoteProcessor;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AdapterPool
     */
    private $paymentDataAdapterPool;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var SamplerManagement
     */
    private $samplerManagement;

    /**
     * @var SamplerManagement
     */
    private $_available_method;

    /**
     * @var HartehankPlaceOrderSync
     */
    private $orderAttributes;

    CONST PAYMENT_TYPE = 'account';

    /**
     * Management constructor.
     * @param HasSubscriptions $quoteChecker
     * @param Processor $quoteProcessor
     * @param CartRepositoryInterface $quoteRepository
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileManagementInterface $profileManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param AdapterPool $paymentDataAdapterPool
     * @param PaymentFactory $paymentFactory
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param Session $checkoutSession
     * @param SamplerManagement $samplerManagement
     * @param PaymentMethodAvailabilityObserverPlugin $availabilityObserverPlugin
     * @param HartehankPlaceOrderSync $orderAttributes
     */
    public function __construct(
        HasSubscriptions $quoteChecker,
        Processor $quoteProcessor,
        CartRepositoryInterface $quoteRepository,
        ProfileRepositoryInterface $profileRepository,
        ProfileManagementInterface $profileManagement,
        OrderRepositoryInterface $orderRepository,
        AdapterPool $paymentDataAdapterPool,
        PaymentFactory $paymentFactory,
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        Session $checkoutSession,
        SamplerManagement $samplerManagement,
        PaymentMethodAvailabilityObserverPlugin $availabilityObserverPlugin,
        HartehankPlaceOrderSync $orderAttributes
    ) {
        $this->quoteChecker = $quoteChecker;
        $this->quoteProcessor = $quoteProcessor;
        $this->quoteRepository = $quoteRepository;
        $this->profileRepository = $profileRepository;
        $this->profileManagement = $profileManagement;
        $this->orderRepository = $orderRepository;
        $this->paymentDataAdapterPool = $paymentDataAdapterPool;
        $this->paymentFactory = $paymentFactory;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->checkoutSession = $checkoutSession;
        $this->samplerManagement = $samplerManagement;
        $this->_available_method = $availabilityObserverPlugin;
        $this->orderAttributes = $orderAttributes;
    }

    /**
     * @param Quote $quote
     * @param null $order
     * @throws \Exception
     */
    public function createProfiles($quote, $order = null)
    {
        //print_r($order->getData());exit;
        if ($this->quoteChecker->check($quote)) {
            $payment = $quote->getPayment();
            $paymentTokenId = null;

            $paymentToken = null;
            if ($order !== null) {
                $order = is_numeric($order)
                    ? $this->orderRepository->get($order)
                    : $order;
                $additionalInformation = $order->getPayment()
                    ->getAdditionalInformation();

                if ($additionalInformation && isset($additionalInformation['aw_sarp_payment_token_id'])) {
                    $paymentTokenId = $additionalInformation['aw_sarp_payment_token_id'];
                    $paymentToken = $this->getPaymentToken($paymentTokenId);
                }
                else{
                    if(in_array($order->getPayment()->getMethod(),$this->_available_method->allowedPaymentMethod())){
                        $paymentToken = $this->createPaymentToken($quote,$payment,$order->getPayment());
                    }
                }
            } else {
                $paymentToken = $this->createPaymentToken($quote, $payment);
            }

            if ($paymentToken && $paymentToken->getTokenId()) {
                $profiles = $this->quoteProcessor->createProfiles($quote);

                return $this->saveAndScheduleProfiles(
                    $profiles,
                    $paymentToken->getTokenId(),
                    $paymentToken->getPaymentMethod(),
                    $order
                );

            }
        }
    }

    /**
     * Create payment token
     *
     * @param Quote $quote
     * @param QuotePayment $quotePayment
     * @return PaymentTokenInterface
     * @throws \Exception
     */
    private function createPaymentToken($quote, $quotePayment,$orderPayment = null)
    {
        $paymentMethod = $quotePayment->getMethod();
        $paymentDataAdapter = $this->paymentDataAdapterPool->getAdapter($paymentMethod);

        /** @var Payment $paymentDataInfo */
        $paymentDataInfo = $this->paymentFactory->create(
            [
                'paymentInfo' => $quotePayment,
                'quote' => $quote
            ]
        );
        $paymentData = $paymentDataAdapter->create($paymentDataInfo);
        $tokenType = $paymentData->getTokenType();

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setPaymentMethod($paymentMethod)
            ->setType($tokenType)
            ->setTokenValue($paymentData->getGatewayToken())
            ->setIsActive(true);
        if ($tokenType == Token::TOKEN_TYPE_CARD) {
            $paymentToken
                ->setExpiresAt(
                    $paymentData->getAdditionalData()->getData('expiration_date')
                )->setDetails(
                    'type',
                    $paymentData->getAdditionalData()->getData('credit_card_type')
                )->setDetails(
                    'maskedCC',
                    $paymentData->getAdditionalData()->getData('credit_card_masked_number')
                )->setDetails(
                    'expirationDate',
                    $paymentData->getAdditionalData()->getData('credit_card_expiration_date')
                );
        }
        $this->paymentTokenRepository->save($paymentToken);
        return $paymentToken;
    }

    /**
     * Get payment token
     *
     * @param int $paymentTokenId
     * @return PaymentTokenInterface|null
     */
    private function getPaymentToken($paymentTokenId)
    {
        try {
            $paymentToken = $this->paymentTokenRepository->get($paymentTokenId);
        } catch (LocalizedException $e) {
            $paymentToken = null;
        }

        return $paymentToken;
    }

    /**
     * Save and schedule profiles
     *
     * @param ProfileInterface[] $profiles
     * @param int $tokenId
     * @param string $paymentMethodCode
     * @param Order|null $order
     * @throws \Exception
     */
    private function saveAndScheduleProfiles($profiles, $tokenId, $paymentMethodCode, $order = null)
    {
        $profileIds = [];
        foreach ($profiles as $profile) {
            $profile
                ->setPaymentTokenId($tokenId)
                ->setPaymentMethod($paymentMethodCode);

            /**
             * Set Delivery Instruction to subscription profile
             * ANAPOLLO-2738
             */
            $orderDeliveryInstruction = $this->getDeliveryInstruction($order);
            if (isset($orderDeliveryInstruction) && !empty($orderDeliveryInstruction)){
                $profile->setDeliveryInstruction($orderDeliveryInstruction);
            }

            if ($order instanceof Order) {
                $profile
                    ->setOrder($order)
                    ->setLastOrderId($order->getEntityId())
                    ->setLastOrderDate($order->getCreatedAt());
            }
            $this->profileRepository->save($profile);
            $profileIds[] = $profile->getProfileId();
        }
        $this->profileManagement->schedule($profiles);
        $this->checkoutSession->setLastProfileIds($profileIds);
        $this->checkoutSession->setLastSuccessProfileIds($profileIds);
        return $profileIds;
    }

    /**
     * @param $order
     * @return mixed|void
     */
    public function getDeliveryInstruction($order){
        $attributes = $this->orderAttributes->getOrderAttributesData($order);
        if(array_key_exists('packing_instruction',$attributes)){
            return $attributes['packing_instruction'];
        }

        return;
    }
}
