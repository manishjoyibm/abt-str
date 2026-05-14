<?php


namespace Abbott\GPAS\Model;

use Abbott\GPAS\Api\Data\Rest\ResponseInterface;
use Abbott\GPAS\Api\QrCodeRepositoryInterface;
use Abbott\GPAS\Exception\UsedCodeException;
use Abbott\GPAS\Logger\Logger;
use Abbott\GPAS\Model\Attribute\Customer\QrCodeId;
use Abbott\GPAS\Model\Rest\SalesRequestFactory as SalesRequestFactory;
use GraphQL\Error\UserError;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Abbott\GPAS\Model\Rest\Adapter;
use Abbott\GPAS\Model\Rest\AuthenticationAttemptsRequestFactory as AuthenticationAttemptsRequestFactory;
use Magento\Framework\Exception\LocalizedException;
use Abbott\GPAS\Model\QrCodeFactory as QrCodeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;

class QrCodeManager implements \Abbott\GPAS\Api\QrCodeManagerInterface
{

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var QrCodeRepositoryInterface
     */
    protected $qrCodeRepository;
    /**
     * @var Adapter
     */
    protected $restClient;
    /**
     * @var AuthenticationAttemptsRequestFactory
     */
    protected $requestFactory;

    /**
     * @var QrCodeFactory
     */
    protected $qrCodeFactory;
    /**
     * @var SalesRequestFactory
     */
    private $salesRequestFactory;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var TimezoneInterface
     */
    private $date;
    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $statusHistoryRepository;

    /**
     * QrCodeManager constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param QrCodeRepositoryInterface $qrCodeRepository
     * @param Adapter $restClient
     * @param QrCodeFactory $qrCodeFactory
     * @param AuthenticationAttemptsRequestFactory $requestFactory
     * @param SalesRequestFactory $salesRequestFactory
     * @param Logger $logger
     * @param TimezoneInterface $date
     * @param OrderStatusHistoryRepositoryInterface $statusHistoryRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        QrCodeRepositoryInterface $qrCodeRepository,
        Adapter $restClient,
        QrCodeFactory $qrCodeFactory,
        AuthenticationAttemptsRequestFactory $requestFactory,
        SalesRequestFactory $salesRequestFactory,
        Logger $logger,
        TimezoneInterface $date,
        OrderStatusHistoryRepositoryInterface $statusHistoryRepository
    ) {

        $this->customerRepository = $customerRepository;
        $this->qrCodeRepository = $qrCodeRepository;
        $this->restClient = $restClient;
        $this->requestFactory = $requestFactory;
        $this->qrCodeFactory = $qrCodeFactory;
        $this->salesRequestFactory = $salesRequestFactory;
        $this->logger = $logger;
        $this->date = $date;
        $this->statusHistoryRepository = $statusHistoryRepository;
    }

    /**
     * @param string $code
     * @param string $ip
     * @param int $customerId
     * @param null $lat
     * @param null $long
     * @return \Abbott\GPAS\Api\Data\QrCodeInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function processInit($code, $ip, $customerId = 0, $lat = null, $long = null)
    {
        if (!$code || !$ip) {
            throw new LocalizedException(__("Cannot process this QR code"));
        }
        $request = $this->requestFactory->create(
            [
                "code" => $code,
                "ip" => $ip,
                "lat" => $lat,
                "long" => $long
            ]
        );
        $qrResponse = $this->restClient->submitQrCode($request);
        if ($qrResponse->isValid()) {
            if ($qrResponse->getPurchaseInformation()) {
                throw new UsedCodeException(__("This qr code has already been used."));
            }
            $qrCode = $this->qrCodeFactory->create();
            $qrCode->load($qrResponse->getCode(), 'code');
            $qrCode->setCode($qrResponse->getCode());
            $qrCode->setIp($ip);
            $qrCode->setLat($lat);
            $qrCode->setLong($long);
            $qrCode->setAdditionalInformation($this->prepareAdditionalInformation($qrResponse));
            $this->qrCodeRepository->save($qrCode);
            if ($customerId > 0) {
                try {
                    $customer = $this->customerRepository->getById($customerId);
                    $customer->setCustomAttribute(QrCodeId::ATTRIBUTE_CODE, $qrCode->getId());
                    $this->customerRepository->save($customer);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        } else {
            throw new LocalizedException(__("Invalid QR code value"));
        }
        return $qrCode;
    }


    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function processSale(OrderInterface $order)
    {
        if ($order->getIncrementId()) {
            try {
                $customer = $this->customerRepository->getById($order->getCustomerId());
                $code = $customer->getCustomAttribute(QrCodeId::ATTRIBUTE_CODE);
                if ($code && !empty($code->getValue())) {
                    $qrCode = $this->qrCodeRepository->getById($code->getValue());
                    $request = $this->salesRequestFactory->create([
                        "code" => $qrCode->getCode(),
                        "orderIncrementId" => $order->getIncrementId(),
                        "orderDate" => $this->date->date($order->getCreatedAt())->format('Y-m-d')
                    ]);
                    $this->restClient->submitSale($request);
                    $comment = $order->addStatusHistoryComment(
                        "GPAS processed successfully. QR Code: ". $qrCode->getCode()
                    );
                    $qrCode->setIsRedeemed(true);
                    $qrCode->setSalesOrderId($order->getId());
                    $this->qrCodeRepository->save($qrCode);
                    $this->statusHistoryRepository->save($comment);
                    $customer->setCustomAttribute(QrCodeId::ATTRIBUTE_CODE, null);
                    $this->customerRepository->save($customer);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $comment = $order->addStatusHistoryComment("GPAS process failed.");
                $this->statusHistoryRepository->save($comment);
            }
        }
        return false;
    }

    /**
     * @param ResponseInterface $qrResponse
     * @return array
     */
    protected function prepareAdditionalInformation(ResponseInterface $qrResponse)
    {
        return [
          "hcp" => $qrResponse->getHcp()
        ];
    }
}
