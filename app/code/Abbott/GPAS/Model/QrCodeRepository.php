<?php


namespace Abbott\GPAS\Model;

use Abbott\GPAS\Api\Data\QrCodeInterface;
use Abbott\GPAS\Api\QrCodeRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QrCodeRepository implements QrCodeRepositoryInterface
{
    const MESSAGE = "This QrCode doesn't exist";
    /**
     * @var ResourceModel\QrCode
     */
    protected $resource;
    /**
     * @var QrCodeFactory
     */
    protected $qrCodeFactory;

    /**
     * QrCodeRepository constructor.
     * @param ResourceModel\QrCode $resource
     * @param QrCodeFactory $qrCodeFactory
     */
    public function __construct(
        \Abbott\GPAS\Model\ResourceModel\QrCode $resource,
        \Abbott\GPAS\Model\QrCodeFactory $qrCodeFactory
    ) {
        $this->resource = $resource;
        $this->qrCodeFactory = $qrCodeFactory;
    }

    /**
     * @param $id
     * @return QrCodeInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $qrCode = $this->qrCodeFactory->create()->load($id);
        if ($qrCode->getId()) {
            return $qrCode;
        } else {
            throw new NoSuchEntityException(__(self::MESSAGE));
        }
    }


    /**
     * @param $code
     * @return QrCodeInterface
     * @throws NoSuchEntityException
     */
    public function getByCode($code)
    {
        $qrCode = $this->qrCodeFactory->create()->load($code, "code");
        if ($qrCode->getId()) {
            return $qrCode;
        } else {
            throw new NoSuchEntityException(__(self::MESSAGE));
        }
    }

    /**
     * @param QrCodeInterface $qrCode
     * @return QrCodeInterface
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(QrCodeInterface $qrCode)
    {
        if ($qrCode->getCode()) {
            $this->resource->save($qrCode);
        } else {
            throw new CouldNotSaveException(__("Cannot save this QrCode"));
        }
        return $qrCode;
    }

    /**
     * @param QrCodeInterface $qrCode
     * @return bool
     * @throws CouldNotDeleteException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function delete(QrCodeInterface $qrCode)
    {
        if ($qrCode->getId()) {
            $this->resource->save($qrCode);
            return true;
        } else {
            throw new CouldNotDeleteException(__(self::MESSAGE));
        }
    }
}
