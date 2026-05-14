<?php
namespace Abbott\Promotions\Api\Data;

interface MessageDataInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    public const MESSAGE = 'message';
    public const RESULT = 'result';
    public const SHIPPING_MESSAGE = 'shipping_message';

    /**
     * GetMessage function
     *
     * @return string
     */
    public function getMessage();

    /**
     * SetMessage function
     *
     * @paarm string $message
     * @return bool
     */
    public function setMessage($message);

    /**
     * GetResult function
     *
     * @return bool
     */
    public function getResult();

    /**
     * SetResult function
     *
     * @paarm bool $result
     * @return bool
     */
    public function setResult($result);

    /**
     * GetShippingMessage function
     *
     * @return string
     */
    public function getShippingMessage();

    /**
     * SetShippingMessage function
     *
     * @paarm string $shippingMessage
     * @return bool
     */
    public function setShippingMessage($shippingMessage);
}
