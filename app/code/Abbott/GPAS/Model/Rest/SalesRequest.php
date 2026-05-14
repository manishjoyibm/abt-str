<?php


namespace Abbott\GPAS\Model\Rest;

class SalesRequest extends AbstractRequest
{
    public const REQUEST_PATH = "sales";

    private $code;
    private $orderIncrementId;
    private $orderDate;

    /**
     * SalesRequest constructor.
     * @param $code
     * @param $orderIncrementId
     * @param $orderDate
     */
    public function __construct($code, $orderIncrementId, $orderDate)
    {
        $this->code = $code;
        $this->orderIncrementId = $orderIncrementId;
        $this->orderDate = $orderDate;
    }

    /**
     * GetRequestBody function
     *
     * @return string[]
     */
    public function getRequestBody()
    {
        return [
            "code" => $this->code,
            "purchaseNumber" => $this->orderIncrementId,
            "purchaseDate" => $this->orderDate
        ];
    }

    /**
     * GetRequestPath function
     *
     * @return string
     */
    public function getRequestPath()
    {
        return self::REQUEST_PATH;
    }
}
