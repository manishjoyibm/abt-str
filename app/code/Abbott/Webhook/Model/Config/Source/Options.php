<?php

namespace Abbott\Webhook\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Options implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Abbott\Webhook\Model\ResourceModel\Webhook\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
    }

    /**
     * ToOptionArray
     *
     * @return array
     */
    public function toOptionArray()
    {
            return $this->getSavedEventList("savedevent");
    }

    /**
     * GetSavedEventList
     *
     * @return event array if savedevent
     * @return else return used event count
     */
    public function getSavedEventList($action)
    {
        $collection = $this->collectionFactory->create();
        $webhookId = $this->request->getParam('webhook_id');
        $options = [];
        if ($webhookId) {
            $collection->addFieldToFilter('webhook_id', $webhookId);
            $event = isset($collection->getData()[0]['event_name']) ? $collection->getData()[0]['event_name'] : null;
            $options[] = [
                    'label' => $event,
                    'value' => $event
                ];
            return $options;
        }
        $eventAvail = [];
        foreach ($collection as $webhook) {
            $eventAvail[] = $webhook->getEventName();
        }
        $events = ['catalog_product_save_commit_after','catalog_product_delete_before',
            'catalog_product_import_bunch_save_after',
            'catalog_product_import_bunch_delete_commit_before',
            'catalog_category_save_after'
        ];
        $count = 0;
        foreach ($events as $event) {
            if (!in_array($event, $eventAvail)) {
                $options[] = [
                    'label' => $event,
                    'value' => $event
                ];
                $count++;
            }
        }
        return ($action == 'savedevent') ? $options : $count;
    }
}
