<?php

namespace Abbott\SmartCart\Controller\Adminhtml\SmartCart;

use Abbott\SmartCart\Model\SmartCart;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Abbott\SmartCart\Model\SmartCartFactory as SmartCartFactory;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\Store\Model\Store;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var SmartCartFactory
     */
    private $smartCartFactory;
    /**
     * @var Collection
     */
    private $ruleCollection;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param Registry $registry
     * @param SmartCartFactory $smartCartFactory
     * @param Collection $ruleCollection
     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        SmartCartFactory $smartCartFactory,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->smartCartFactory = $smartCartFactory;
        $this->ruleCollection = $ruleCollection;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }
        /** @var SmartCart $model */
        $model = $this->initCart();
        try {
            $model->setData($data['cart']);
            $model->setProducts(json_decode($data['products'], true));
            if (isset($data["cart"]['discount_rule'])) {
                $ruleCollection = $this->ruleCollection->create();
                $ruleCollection->addFieldToFilter("code", $data["cart"]['discount_rule']);
                $rule = $ruleCollection->getFirstItem();
                if ($rule->getId()) {
                    $model->setDiscountRuleId($rule->getId());
                } else {
                    $model->setDiscountRuleId(null);
                }
            }
            $model->save();
            $this->_getSession()->setFormData(false);
            $this->messageManager->addSuccess(__('You saved the smartcart.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $redirectBack = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $redirectBack = true;
            $this->messageManager->addError(__('We cannot save the smartcart.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        return ($redirectBack)
            ? $resultRedirect->setPath('*/*/edit', [
                'id' => $model->getId(),
                'store' => $model->getStoreId()
            ])
            : $resultRedirect->setPath('*/*/');
    }

    /**
     * Load Smart Cart from request
     *
     * @param $idFieldName
     * @return SmartCart
     */
    protected function initCart($idFieldName = 'id')
    {
        $bannerId = (int)$this->getRequest()->getParam($idFieldName);
        $model = $this->smartCartFactory->create();
        if ($bannerId) {
            $model->load($bannerId);
        }
        if (!$this->registry->registry('smartcart')) {
            $this->registry->register('smartcart', $model);
        }
        return $model;
    }
}
