<?php

namespace Abbott\AbbottReport\Model;

use Abbott\AbbottReport\Api\Data\AbbottExportInfoInterface;

class AbbottExportInfo implements AbbottExportInfoInterface
{
    /**
     * @var string
     */
    private $toGratis;

    /**
     * @var string
     */
    private $fromGratis;

    /**
     * @var int
     */
    private $storeId;


    public function getToGratis()
    {
        return $this->toGratis;
    }

     /**
     * @inheritdoc
     */
    public function setToGratis($toGratis)
    {
        $this->toGratis = $toGratis;
    }

    /**
     * @inheritdoc
     */
    public function setFromGratis($fromGratis)
    {
        $this->fromGratis = $fromGratis;
    }



    public function getFromGratis()
    {
        return $this->fromGratis;
    }



    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

}
