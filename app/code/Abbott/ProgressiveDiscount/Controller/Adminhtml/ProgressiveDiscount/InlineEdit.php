<?php

namespace Abbott\ProgressiveDiscount\Controller\Adminhtml\ProgressiveDiscount;

use Abbott\ProgressiveDiscount\Model\ManageMonthlySubscriptions;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception;

/**
 * Monthly Subscription grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * @var PageRepositoryInterface
     */
    protected $jsonFactory;

    /**
     * @var ManageMonthlySubscriptions
     */
    protected $manageSubscription;

    /**
     * Constructor function
     *
     * @param Magento\Backend\App\Action\Context $context
     * @param Magento\Framework\Controller\Result\JsonFactor $jsonFactory
     * @param Abbott\ProgressiveDiscount\Model\ManageMonthlySubscriptions $manageSubscription
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        ManageMonthlySubscriptions $manageSubscription
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->manageSubscription = $manageSubscription;
    }

    /**
     * Process the request
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $entityId) {
                    $model = $this->manageSubscription->load($entityId);
                    try {
                        $this->setSubscritpionData($model, $postItems[$entityId]);
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = "[Error:]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * SetSubscritpionData
     *
     * @param ManageMonthlySubscriptions $model
     * @param $items
     * @return $this
     */
    private function setSubscritpionData(ManageMonthlySubscriptions $model, $items)
    {
        $model->setData(array_merge($model->getData(), $items));
        return $this;
    }
}
