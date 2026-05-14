<?php
namespace Abbott\Targetbase\Controller\Adminhtml\Targetbase;

class Filesync extends \Magento\Backend\App\Action
{
    /**
     * @var \Abbott\Targetbase\Model\Syncdata
     */
    protected $syncdata;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Abbott\Targetbase\Model\Syncdata $syncdata
    ) {
        $this->syncdata = $syncdata;
        parent::__construct($context);
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
