<?php


namespace Abbott\GPAS\Model;

use Abbott\GPAS\Api\Data\QrCodeInterface;
use Abbott\GPAS\Model\ResourceModel\QrCode as QrCodeResource;

class QrCode extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    QrCodeInterface
{

    /**
     * string
     */
    const CACHE_TAG = 'abbott_gpas_qrcode';

    /**
     * @var string
     */
    protected $_cacheTag = 'abbott_gpas_qrcode';

    /**
     * @var string
     */
    protected $_eventPrefix = 'abbott_gpas_qrcode';

   /**
    * Construct function
    *
    * @return void
    */
    protected function _construct()
    {
        $this->_init(QrCodeResource::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return $this->getData(self::ATTR_CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function setCode($code)
    {
        return $this->setData(self::ATTR_CODE, $code);
    }

    /**
     * {@inheritDoc}
     */
    public function getIp()
    {
        return $this->getData(self::ATTR_IP);
    }

    /**
     * {@inheritDoc}
     */
    public function setIp($ip)
    {
        return $this->setData(self::ATTR_IP, $ip);
    }

    /**
     * {@inheritDoc}
     */
    public function getLat()
    {
        return $this->getData(self::ATTR_LAT);
    }

    /**
     * {@inheritDoc}
     */
    public function setLat($lat)
    {
        return $this->setData(self::ATTR_LAT, $lat);
    }

    /**
     * {@inheritDoc}
     */
    public function getLong()
    {
        return $this->getData(self::ATTR_LONG);
    }

    /**
     * {@inheritDoc}
     */
    public function setLong($long)
    {
        return $this->setData(self::ATTR_LONG, $long);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($date)
    {
        return $this->setData(self::CREATED_AT, $date);
    }
    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }
    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($date)
    {
        return $this->setData(self::UPDATED_AT, $date);
    }
    /**
     * {@inheritDoc}
     */
    public function getIsRedeemed()
    {
        return $this->getData(self::IS_REDEEMED);
    }
    /**
     * {@inheritDoc}
     */
    public function setIsRedeemed($redeemed)
    {
        return $this->setData(self::IS_REDEEMED, $redeemed);
    }
    /**
     * {@inheritDoc}
     */
    public function setAdditionalInformation($additionalInformation)
    {
        return $this->setData(self::ADDITIONAL_INFORMATION, $additionalInformation);
    }
    /**
     * {@inheritDoc}
     */
    public function getAdditionalInformation()
    {
        return $this->getData(self::ADDITIONAL_INFORMATION);
    }
}
