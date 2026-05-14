<?php

namespace Abbott\Promotions\Model\Data;

use Abbott\Promotions\Api\Data\MessageDataInterface;

class Message extends \Magento\Framework\Api\AbstractExtensibleObject implements MessageDataInterface
{
    /**
     * @var $messages
     */
    protected $messages;
    /**
     * @var $results
     */
    protected $results;
    /**
     * @var $shippingMessage
     */
    protected $shippingMessage;

    /**
     * GetMessage function
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->messages;
    }

     /**
      * @inheritDoc
      */
    public function setMessage($message)
    {
        $this->messages = $message;
    }

    /**
     * GetResult function
     *
     * @return bool
     */
    public function getResult()
    {
        return $this->results;
    }

    /**
     * SetResult function
     *
     * @param $result
     * @return bool|void
     */
    public function setResult($result)
    {
        $this->results = $result;
    }

    /**
     * @inheritDoc
     */
    public function getShippingMessage()
    {
        return $this->shippingMessage;
    }

     /**
      * SetShippingMessage function
      *
      * @param string $shippingMessage
      * @return void
      */
    public function setShippingMessage($shippingMessage)
    {
        $this->shippingMessage = $shippingMessage;
    }
}
