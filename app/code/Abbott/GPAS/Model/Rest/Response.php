<?php


namespace Abbott\GPAS\Model\Rest;

use Abbott\GPAS\Api\Data\Rest\ResponseInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Response extends \Magento\Framework\DataObject implements ResponseInterface
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * Response constructor.
     * @param Json $jsonSerializer
     * @param array $data
     */
    public function __construct(
        Json $jsonSerializer,
        array $data = []
    ) {
        parent::__construct($data);
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @{@inheritdoc}
     */
    public function isValid()
    {
        return $this->getResult() == self::RESPONSE_VALID;
    }

    /**
     * @{@inheritdoc}
     */
    public function getResult()
    {
        return $this->getData(self::RESULT);
    }

    /**
     * @{@inheritdoc}
     */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /**
     * @{@inheritdoc}
     */
    public function getLocalisedProductData()
    {
        return $this->getData(self::LOCALISED_PRODUCT_DATA);
    }

    /**
     * @{@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * @{@inheritdoc}
     */
    public function getAuthenticationAttempts()
    {
        return $this->getData(self::AUTHENTICATION_ATTEMPTS);
    }

    /**
     * @{@inheritdoc}
     */
    public function getLocation()
    {
        return $this->getData(self::LOCATION);
    }

    /**
     * @{@inheritdoc}
     */
    public function getReason()
    {
        return $this->getData(self::REASON);
    }

    /**
     * @{@inheritdoc}
     */
    public function getPurchaseInformation()
    {
        return $this->getData(self::PURCHASE_INFORMATION);
    }

    /**
     * @{@inheritdoc}
     */
    public function getHcp()
    {
        return $this->getData(self::HCP);
    }


    /**
     * @{@inheritdoc}
     */
    public function parseResponse($response)
    {
        $responseData = $this->jsonSerializer->unserialize($response);
        $this->setData($responseData);
        return $this;
    }
}
