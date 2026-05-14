<?php

namespace Abbott\DPS\Cron;

use Abbott\DPS\Helper\Data;
use Abbott\DPS\Model\DpsListItem;
use Abbott\DPS\Model\ResourceModel\DpsListItem\CollectionFactory as DpsListItemCollectionFactory;
use Abbott\DPS\Model\DpsListItemFactory;
use Abbott\DPS\Model\DpsListItemAddressFactory;
use Exception;
use GuzzleHttp\ClientFactory as ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory as ResponseFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Http\Message\ResponseInterface;

class DpsListSync
{

    public const ALLOWED_COUNTRY_CODE = "US";

    public const ALLOWED_TYPES = [null, "Entity", "Individual"];
    /**
     * @var Data
     */
    protected Data $data;

    /**
     * @var ClientFactory
     */
    protected ClientFactory $clientFactory;

    /**
     * @var ResponseFactory
     */
    protected ResponseFactory $responseFactory;

    /**
     * @var Json
     */
    protected Json $jsonSerializer;

    /**
     * @var DpsListItemFactory
     */
    protected DpsListItemFactory $dpsListItemFactory;

    /**
     * @var DpsListItemAddressFactory
     */
    protected DpsListItemAddressFactory $dpsListItemAddressFactory;

    /**
     * @var array
     */
    protected array $dpsRecordIds = [];

    /**
     * @var DpsListItemCollectionFactory
     */
    protected DpsListItemCollectionFactory $dpsListItemCollectionFactory;

    /**
     * DpsListSync constructor.
     * @param Data $data
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Json $jsonSerializer
     * @param DpsListItemFactory $dpsListItemFactory
     * @param DpsListItemAddressFactory $dpsListItemAddressFactory
     * @param DpsListItemCollectionFactory $dpsListItemCollectionFactory
     */
    public function __construct(
        Data $data,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        Json $jsonSerializer,
        DpsListItemFactory $dpsListItemFactory,
        DpsListItemAddressFactory $dpsListItemAddressFactory,
        DpsListItemCollectionFactory $dpsListItemCollectionFactory
    ) {
        $this->data = $data;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->dpsListItemFactory = $dpsListItemFactory;
        $this->dpsListItemAddressFactory = $dpsListItemAddressFactory;
        $this->dpsListItemCollectionFactory = $dpsListItemCollectionFactory;
    }

    /**
     * Method execute
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        $fileUrl = $this->data->getFileUrl();
        if ($this->data->isCronEnabled() && $fileUrl) {
            $response = $this->pullFile($fileUrl);
            if ($response->getStatusCode() == 200) {
                $this->processResponse($response);
            }
        }
    }

    /**
     * Execute pullFile
     *
     * @param string|null $fileUrl
     * @return false|Response|ResponseInterface
     */
    public function pullFile(string $fileUrl = null): false|Response|ResponseInterface
    {
        if (!$fileUrl) {
            return false;
        }
        $client = $this->clientFactory->create();

        try {
            $response = $client->request(
                Request::HTTP_METHOD_GET,
                $fileUrl
            );
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }
        return $response;
    }

    /**
     * Method processResponse
     *
     * @param mixed $response
     * @return void
     * @throws Exception
     */
    public function processResponse(mixed $response): void
    {
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();
        $objects = $this->jsonSerializer->unserialize($responseContent);
        $this->getExistingDpsRecordIds();
        foreach ($objects["results"] as $object) {
            $dpsItem = $this->prepareDpsItem($object);
            if ($dpsItem) {
                $dpsItem->save();
            }
        }
    }

    /**
     * Method prepareDpsItem
     *
     * @param array $data
     * @return DpsListItem|false
     */
    protected function prepareDpsItem(array $data): false|DpsListItem
    {
        if (!in_array($data["type"] ?? null, self::ALLOWED_TYPES)) {
            return false;
        }
        if (in_array($data["id"], $this->getExistingDpsRecordIds())) {
            return false;
        }
        $dpsListItem = $this->dpsListItemFactory->create();
        $dpsListItem->setStartDate($data["start_date"] ?? null);
        $dpsListItem->setEndDate($data["end_date"] ?? null);
        $dpsListItem->setName($data["name"]);
        $dpsListItem->setSource($data["source"] ?? null);
        $dpsListItem->setType($data["type"] ?? null);
        $dpsListItem->setReferenceId($data["id"]);
        $addresses = [];
        if (!isset($data["addresses"])) {
            return false;
        }
        foreach ($data["addresses"] as $address) {
            if (!empty($address["address"]) &&
                !empty($address["city"]) &&
                !empty($address["state"]) &&
                !empty($address["postal_code"]) &&
                $address["country"] == self::ALLOWED_COUNTRY_CODE) {
                $addressObject = $this->dpsListItemAddressFactory->create();
                $addressObject->setAddress($address["address"]);
                $addressObject->setCity($address["city"]);
                $addressObject->setState($address["state"]);
                $addressObject->setPostalCode($address["postal_code"]);
                $addressObject->setCountry($address["country"]);
                $addresses[] = $addressObject;
            }
        }
        if (empty($addresses)) {
            return false;
        }
        $dpsListItem->setAddresses($addresses);
        return $dpsListItem;
    }

    /**
     * Method getExistingDpsRecordIds
     *
     * @return array
     */
    protected function getExistingDpsRecordIds(): array
    {
        if (!$this->dpsRecordIds) {
            $dpsListItemCollection = $this->dpsListItemCollectionFactory->create();
            $dpsListItemCollection->addFieldToSelect("reference_id");
            foreach ($dpsListItemCollection as $dpsListItem) {
                $this->dpsRecordIds[] = $dpsListItem->getReferenceId();
            }
        }
        return $this->dpsRecordIds;
    }
}
