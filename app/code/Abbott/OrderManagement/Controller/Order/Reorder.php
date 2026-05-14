<?php

namespace Abbott\OrderManagement\Controller\Order;

use Abbott\GPAS\Model\Attribute\Product\IsGpas;
use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Registry;
use Abbott\MyAccount\Helper\Data as AccountHelper;

class Reorder extends \Magento\Sales\Controller\AbstractController\Reorder implements OrderInterface
{

    public $storeManager;
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var Registry
     */
    protected $coreRegistry;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\AbstractController\OrderLoaderInterface $orderLoader
     * @param Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\AbstractController\OrderLoaderInterface $orderLoader,
        Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->orderLoader = $orderLoader;
        $this->coreRegistry = $registry;
        $this->storeManager = $storeManager;
        parent::__construct($context, $orderLoader, $registry);
        $this->cart = $cart;
    }

    /**
     * Action for reorder
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $order = $this->coreRegistry->registry('current_order');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();


        $items = $order->getItemsCollection();
        try {
            foreach ($items as $item) {
                /* Due to the nature of GPAS products, they should only be purchased with a valid QR Code, at the point
                   of reorder, Qr Code will be likely considered as "redeemed" so we should not reorder these types of
                   products */
                if (!$item->getProduct()->getData(IsGpas::ATTRIBUTE_CODE)) {
                    $this->cart->addOrderItem($item);
                }
            }
            $this->cart->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage($e->getMessage());
            } else {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            return $resultRedirect->setPath('*/*/history');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            if ($this->storeManager->getStore()->getId() == AccountHelper::GLU_STORE_ID) {
                return $resultRedirect->setPath('*/*/history');
            } else {
                return $resultRedirect->setPath('checkout/cart');
            }
        }
        if ($this->storeManager->getStore()->getId() == AccountHelper::GLU_STORE_ID) {
            return $resultRedirect->setPath('checkout');
        } else {
            return $resultRedirect->setPath('checkout/cart');
        }
    }
}
