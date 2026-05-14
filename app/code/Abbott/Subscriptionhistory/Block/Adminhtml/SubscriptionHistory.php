<?php


namespace Abbott\Subscriptionhistory\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Json\EncoderInterface;
use Abbott\Subscriptionhistory\Model\ResourceModel\Subscriptionhistory\CollectionFactory;
use Abbott\Subscriptionhistory\Helper\Data;

class SubscriptionHistory extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'subscription/subscription_history.phtml';

    /**
     * @var
     */
    protected $blockGrid;
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var CollectionFactory
     */
    protected $subscriptionHistory;

    /**
     * @var Data
     */
    protected $subscriptionHistoryHelper;

    /**
     * @var mixed
     */
    protected $profileId;

    /**
     * SubscriptionHistory constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param CollectionFactory $subscriptionHistory
     * @param Data $subscriptionHistoryHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        CollectionFactory $subscriptionHistory,
        Data $subscriptionHistoryHelper,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->subscriptionHistory = $subscriptionHistory;
        $this->subscriptionHistoryHelper = $subscriptionHistoryHelper;
        $this->profileId = $context->getRequest()->getParam('profile_id');
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Abbott\Subscriptionhistory\Block\Adminhtml\Tab\SubscriptionHistoryGrid',
                'subscription.history.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        if ($this->getSubscriptionHistorySetting()) {
            return $this->getBlockGrid()->toHtml();
        }
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSubscriptionHistorySetting()
    {
        $storeId = $this->subscriptionHistoryHelper->getProfile($this->profileId)->getStoreId();
        return $this->subscriptionHistoryHelper->getSubscriptionHistoryStatus($storeId);
    }
}
