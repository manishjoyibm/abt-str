<?php


namespace Abbott\GPAS\Model\Rest;

class AuthenticationAttemptsRequest extends AbstractRequest
{

    const REQUEST_PATH = "authenticationAttempts/basic";
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $ip;
    /**
     * @var string|null
     */
    private $lat;
    /**
     * @var string|null
     */
    private $long;

    /**
     * Request constructor.
     * @param string $code
     * @param string $ip
     * @param string|null $lat
     * @param string|null $long
     */
    public function __construct(
        $code,
        $ip,
        $lat = null,
        $long = null
    ) {
        $this->code = $code;
        $this->ip = $ip;
        $this->lat = $lat;
        $this->long = $long;
    }


    /**
     * @return string[]
     */
    public function getRequestBody()
    {
        $response = [
            "callerIp" => $this->ip,
            "code" => $this->code
        ];
        if ($this->lat && $this->long) {
            $response["callerLocation"] = [
                "latitude" => $this->lat,
                "longitude" => $this->long,
                "confidence" => 100
            ];
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getRequestPath()
    {
        return self::REQUEST_PATH;
    }
}
