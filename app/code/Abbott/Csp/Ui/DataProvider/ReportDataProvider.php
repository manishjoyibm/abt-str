<?php
namespace Abbott\Csp\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Abbott\Csp\Model\ResourceModel\Report\CollectionFactory;

class ReportDataProvider extends AbstractDataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $items = [];
        foreach ($this->collection->getItems() as $model) {
            $items[] = $model->getData();
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $items
        ];
    }
}


