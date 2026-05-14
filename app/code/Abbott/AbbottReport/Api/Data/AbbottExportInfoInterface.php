<?php


namespace Abbott\AbbottReport\Api\Data;


interface AbbottExportInfoInterface
{
    /**
     * Return Start date.
     * @return string
     */
    public function getToGratis();

    /**
     * Set Start date.
     * @param string $toGratis
     * @return void
     */
    public function setToGratis($toGratis);

    /**
     * Return End date.
     *
     * @return string
     */
    public function getFromGratis();

    /**
     * Set End date.
     * @param string $toGratis
     * @return void
     */
    public function setFromGratis($fromGratis);

    /**
     * get getStoreId
     * @return int
     */
    public function getStoreId();

    /**
     * Set setStoreId.
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId);


}
