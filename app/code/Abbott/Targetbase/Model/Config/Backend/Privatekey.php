<?php
namespace Abbott\Targetbase\Model\Config\Backend;

class Privatekey extends \Magento\Framework\App\Config\Value
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'config_data';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'config_data';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->config = $config;
        $this->cacheTypeList = $cacheTypeList;
        $this->file = $file;
        $this->directoryList = $directoryList;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $varPath = $this->getVarPath();
              $keyfilepath = $varPath . 'Targetbase/' . 'targetbase_import-private.key';
              $fileExists = $this->file->isExists($keyfilepath);
            if ($fileExists==1) {
                $this->file->deleteFile($keyfilepath);
            }
        }
        return parent::afterSave();
    }
    /**
     * For generating path till var folder with /
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getVarPath()
    {
        return $this->directoryList->getPath('var') . '/';
    }
}
