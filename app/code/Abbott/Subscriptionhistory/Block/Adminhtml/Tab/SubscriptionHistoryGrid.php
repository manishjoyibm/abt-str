<?php


namespace Abbott\Subscriptionhistory\Block\Adminhtml\Tab;

use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Abbott\Subscriptionhistory\Model\SubscriptionhistoryFactory;
use Magento\Framework\Escaper;

class SubscriptionHistoryGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var SubscriptionhistoryFactory
     */
    protected $subscriptionHistory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * SubscriptionHistoryGrid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param SubscriptionhistoryFactory $subscriptionHistory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        SubscriptionhistoryFactory $subscriptionHistory,
        Escaper $escaper,
        array $data = []
    ) {
        $this->subscriptionHistory = $subscriptionHistory;
        $this->escaper = $escaper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('subscription_history_logs');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);

        $this->setSaveParametersInSession(true);
    }

    /**
     * @return SubscriptionHistoryGrid
     */
    protected function _prepareCollection()
    {
        $collection = $this->subscriptionHistory->create()
                           ->getCollection()
                           ->addFieldToFilter(
                               'profile_id',
                               $this->escaper->escapeHtml($this->getRequest()->getParam('profile_id'))
                           );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'number',
            ]
        );
        $this->addColumn(
            'event_name',
            [
                'header' => __('Event Name'),
                'index' => 'event_name',
            ]
        );

        $this->addColumn(
            'before_value',
            [
                'header' => __('Message'),
                'index' => 'before_value',
                'renderer' => 'Abbott\Subscriptionhistory\Block\Adminhtml\Subscriptionhistory\Grid\Renderer',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'mbo_user',
            [
                'header' => __('MBO Username'),
                'index' => 'mbo_user',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created Date'),
                'index' => 'created_at',
                'type' => 'datetime',
                'renderer' => 'Abbott\Subscriptionhistory\Block\Adminhtml\Subscriptionhistory\Grid\DateRenderer',
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('aw_sarp2_history/index/grids', ['_current' => true]);
    }
}
