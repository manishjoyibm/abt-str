<?php

namespace Abbott\CreditCards\Model;

use Magento\Framework\App\ResourceConnection;

class AddressPaymentTokenLink
{

    public const TABLENAME = 'payment_address_link';
    public const ADDRESS_ID = 'address_id';
    public const PAYMENT_TOKEN_ID = 'payment_token_id';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Constructor
     *
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Add link between payment token and address.
     *
     * @param  int $paymentTokenId
     * @param  int $addressId
     * @return bool
     */
    public function addLinkToAddressPayment($paymentTokenId, $addressId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName(self::TABLENAME))
            ->where(self::ADDRESS_ID.' = ?', (int) $addressId)
            ->where(self::PAYMENT_TOKEN_ID.' = ?', (int) $paymentTokenId);

        if (!empty($connection->fetchRow($select))) {
            return true;
        }

        return 1 === $connection->insert(
            $this->resource->getTableName(self::TABLENAME),
            [self::ADDRESS_ID => (int) $addressId, self::PAYMENT_TOKEN_ID => (int) $paymentTokenId]
        );
    }

    /**
     * Get address id by payment token id.
     *
     * @param  int $paymentTokenId
     * @return int
     */
    public function getAddressIdByPaymentId($paymentTokenId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection
            ->select()
            ->from($this->resource->getTableName(self::TABLENAME), self::ADDRESS_ID)
            ->where(self::PAYMENT_TOKEN_ID.' = ?', (int) $paymentTokenId);
        return $connection->fetchOne($select);
    }
}
