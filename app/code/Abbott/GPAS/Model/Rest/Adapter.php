<?php


namespace Abbott\GPAS\Model\Rest;

use Abbott\GPAS\Api\Data\Rest\ResponseInterface;
use Abbott\GPAS\Api\Data\Rest\ResponseInterfaceFactory as ResponseInterfaceFactory;
use Abbott\GPAS\Helper\Data;
use Abbott\GPAS\Logger\Logger;
use Amasty\Base\Debug\Log;
use Magento\Framework\Exception\LocalizedException;
use Laminas\Http\Client;

class Adapter
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var ResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Data
     */
    private $helper;

    /**
     * Adapter constructor.
     * @param Client $client
     * @param ResponseInterfaceFactory $responseFactory
     * @param Data $helper
     * @param Logger $logger
     */
    public function __construct(
        Client $client,
        ResponseInterfaceFactory $responseFactory,
        Data $helper,
        Logger $logger
    ) {

        $this->client = $client;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Create order.
     *
     * @param AuthenticationAttemptsRequest $request
     * @return ResponseInterface|bool
     */
    public function submitQrCode(AuthenticationAttemptsRequest $request)
    {
        try {
            $uri = $this->prepareUri($request);
            $requestBody = $request->getRequestBodyJson();
            $rawResponse = $this->post($uri, $requestBody);
            $this->logger->info($requestBody);
            /** @var ResponseInterface $response */
            $response = $this->responseFactory->create();
            $response->parseResponse($rawResponse->getBody());
            return $response;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }

    /**
     * Create order.
     *
     * @param SalesRequest $request
     * @return boolean
     * @throws LocalizedException
     */
    public function submitSale(SalesRequest $request)
    {
        try {
            $uri = $this->prepareUri($request);
            $requestBody = $request->getRequestBodyJson();
            $this->post($uri, $requestBody);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__('Could not submit the sale'));
        }

        return true;
    }

    /**
     * GetEndPoint function
     *
     * @return string
     */
    private function getEndpoint()
    {
        return $this->helper->getApiUrl();
    }

    /**
     * GetHeaders function
     *
     * @return string[]
     */
    private function getHeaders()
    {
        return [
            "apiKey" => $this->helper->getApiKey(),
            "Accept" => "application/json",
            "Content-Type" => "application/json"
        ];
    }

    /**
     * Post function
     *
     * @param $uri
     * @param $rawBody
     * @return \Laminas\Http\Response
     */
    public function post($uri, $rawBody)
    {
        $this->logger->info(sprintf("REQUEST: %s\n%s", $uri, $rawBody));
        $this->client->setHeaders($this->getHeaders());
        $this->client->setUri($uri);
        $this->client->setMethod(\Laminas\Http\Request::METHOD_POST);
        $this->client->setRawBody($rawBody);
        $response = $this->client->send();
        $this->logger->info(sprintf("RESPONSE[%s]: %s\n%s", $response->getStatusCode(), $uri, $response->getBody()));
        return $response;
    }

    /**
     * PrepareURI function
     *
     * @param AbstractRequest $request
     * @return string
     */
    public function prepareUri(AbstractRequest $request)
    {
        return sprintf(
            '%s/'.$request->getRequestPath().'?%s',
            $this->getEndpoint(),
            http_build_query($request->getRequestParams())
        );
    }
}
