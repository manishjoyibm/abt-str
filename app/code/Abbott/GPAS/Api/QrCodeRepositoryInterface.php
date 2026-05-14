<?php


namespace Abbott\GPAS\Api;


use Abbott\GPAS\Api\Data\QrCodeInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface QrCodeRepositoryInterface
 * @package Abbott\GPAS\Api
 */
interface QrCodeRepositoryInterface
{

    /**
     * @param $id
     * @return QrCodeInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param $code
     * @return QrCodeInterface
     * @throws NoSuchEntityException
     */
    public function getByCode($code);

    /**
     * @param QrCodeInterface $qrCode
     * @return QrCodeInterface
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(QrCodeInterface $qrCode);

    /**
     * @param QrCodeInterface $qrCode
     * @return bool
     * @throws CouldNotDeleteException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function delete(QrCodeInterface $qrCode);

}
