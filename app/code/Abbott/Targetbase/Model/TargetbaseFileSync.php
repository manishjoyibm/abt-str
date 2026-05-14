<?php
namespace Abbott\Targetbase\Model;

class TargetbaseFileSync extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Abbott\Targetbase\Model\Syncdata
     */
    protected $syncdata;

    /**
     * TargetbaseFileSync constructor.
     * @param Syncdata $syncdata
     */
    public function __construct(
        Syncdata $syncdata
    ) {
        $this->syncdata = $syncdata;
    }

    /**
     * This function helps to sync the files to SFTP
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $this->syncdata->syncAllData();
    }
}
