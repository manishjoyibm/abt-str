<?php
namespace Abbott\Targetbase\Controller\Adminhtml\Targetbase;

class Connectioncheck extends \Magento\Backend\App\Action
{
    /**
     * @var \Abbott\Targetbase\Model\Connectioncheck
     */
    protected $connectioncheck;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Abbott\Targetbase\Model\Connectioncheck $connectioncheck
    ) {
        $this->connectioncheck = $connectioncheck;
        parent::__construct($context);
    }

    /**
     * This function helps to check the SFTP connection status
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $this->connectioncheck->connectionCheckStatus();
    }
}
