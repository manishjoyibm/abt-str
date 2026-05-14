<?php


namespace Abbott\GPAS\Model\Rest;

/**
 * Class AbstractRequest
 * @package Abbott\GPAS\Model\Rest
 */
abstract class AbstractRequest
{

    /**
     * GetRequestParams function
     *
     * @return array
     */
    public function getRequestParams()
    {
        return [];
    }

    /**
     * GetRequestBody function
     *
     * @return array
     */
    public function getRequestBody()
    {
        return [];
    }

    /**
     * GetRequestBodyJson function
     *
     * @return string
     */
    public function getRequestBodyJson()
    {
        return json_encode($this->getRequestBody());
    }

    /**
     * GetRequestPath
     *
     * @return string
     */
    abstract public function getRequestPath();
}
