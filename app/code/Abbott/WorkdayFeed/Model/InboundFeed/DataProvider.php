<?php

namespace Abbott\WorkdayFeed\Model\InboundFeed;

use Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\Collection;
use Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

class DataProvider extends ModifierPoolDataProvider
{
    public $storeManager;
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $inboundfeedCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $inboundfeedCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $inboundfeedCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta): array
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array|null
     * @throws NoSuchEntityException
     */
    public function getData(): ?array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
            if ($model->getFileName()) {
                $m['file_name'][0]['name'] = $model->getFileName();
                $m['file_name'][0]['url'] = $this->getMediaUrl().$model->getFileName();
            }
        }

        $data = $this->dataPersistor->get('workdayfeed_inboundfeed');
        if (!empty($data)) {
            $inboundfeed = $this->collection->getNewEmptyItem();
            $inboundfeed->setData($data);
            $this->loadedData[$inboundfeed->getId()] = $inboundfeed->getData();
            $this->dataPersistor->clear('workdayfeed_inboundfeed');
        }

        return $this->loadedData;
    }

    /**
     * Method getMediaUrl
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl(): string
    {
        return $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'Abbott/WorkdayFeed/';
    }
}
