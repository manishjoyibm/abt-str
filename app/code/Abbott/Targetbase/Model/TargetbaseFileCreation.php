<?php
namespace Abbott\Targetbase\Model;

class TargetbaseFileCreation extends \Magento\Framework\Model\AbstractModel
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

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * TargetbaseFileCreation constructor.
     * @param Exportorderdata $exportorderdata
     * @param Exportdata $exportdata
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Exportorderdata $exportorderdata,
        Exportdata $exportdata,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->exportorderdata = $exportorderdata;
        $this->exportdata = $exportdata;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * This cron is used to create both customer and order files
     *
     * @return void
     */
    public function execute()
    {
        $this->exportorderdata->exportData();
        $this->exportdata->exportCustomerData();

        if ($this->scopeConfig->getValue(Exportorderdata::ONETIMESYNC)) {
            $this->configWriter->save(Exportorderdata::ONETIMESYNC, 0, $this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        }
        $types = ['config'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
