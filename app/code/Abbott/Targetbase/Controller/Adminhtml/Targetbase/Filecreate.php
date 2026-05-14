<?php
namespace Abbott\Targetbase\Controller\Adminhtml\Targetbase;

class Filecreate extends \Magento\Backend\App\Action
{
    /**
     * @var \Abbott\Targetbase\Model\Exportorderdata
     */
    protected $exportorderdata;
    /**
     * @var \Abbott\Targetbase\Model\Exportdata
     */
    protected $exportdata;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface;
     */
    protected $cacheTypeList;
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool;
     */
    protected $cacheFrontendPool;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Abbott\Targetbase\Model\Exportorderdata $exportorderdata,
        \Abbott\Targetbase\Model\Exportdata $exportdata,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->exportorderdata = $exportorderdata;
        $this->exportdata = $exportdata;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context);
    }

    /**
     * This function is used to create both customer and order file
     *
     * @return void
     */
    public function execute()
    {
        $this->exportorderdata->exportData();
        $this->exportdata->exportCustomerData();
        $types = ['config'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
