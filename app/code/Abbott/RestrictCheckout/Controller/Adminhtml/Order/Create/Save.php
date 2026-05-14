<?php

namespace Abbott\RestrictCheckout\Controller\Adminhtml\Order\Create;

use Abbott\MetabolicOrdering\Helper\Data;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;
use Abbott\RestrictCheckout\Model\AdminRestriction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Helper\Product;
use Magento\Framework\Escaper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Exception\PaymentException;

class Save extends \Magento\Sales\Controller\Adminhtml\Order\Create\Save
{
    public $restriction;
    const REDIRECT_PATH = 'sales/order/index';
    const VIEW_PATH = 'sales/order/view';

    protected $timezoneInterface;
    /**
     * @var helper
     */
    protected $helper;
    /**
     * @var authSession
     */
    protected $authSession;
    /**
     * @var MetabolicFactory
     */
    protected $metabolicModelFactory;

    public function __construct(
        Context $context,
        TimezoneInterface $timezoneInterface,
        Product $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Session $authSession,
        MetabolicFactory $metabolicModelFactory,
        Data $helper,
        AdminRestriction $restriction
    ) {
        $this->restriction = $restriction;
        $this->helper = $helper;
        $this->timezoneInterface = $timezoneInterface;
        $this->metabolicModelFactory = $metabolicModelFactory;
        $this->authSession = $authSession;
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);
    }

    /**
     * Saving quote and create order
     *
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $path = 'sales/*/';
        $pathParams = [];

        try {
            // check if the creation of a new customer is allowed
            if (
                !$this->_authorization->isAllowed('Magento_Customer::manage')
                && !$this->_getSession()->getCustomerId()
                && !$this->_getSession()->getQuote()->getCustomerIsGuest()
            ) {
                return $this->resultForwardFactory->create()->forward('denied');
            }

            // To check if metabolic product is allocated to customer
            if ($this->helper->getModuleEnable()) {
                $items = $this->_getSession()->getQuote()->getAllItems();
                $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
                foreach ($this->helper->filterProducts() as $value) {
                    $metabolicSkus[] = $value;
                }
                foreach ($items as $item) {
                    if (in_array($item->getSku(), $metabolicSkus)) {
                        $data['sku'] = $item->getSku();
                        $data['customer_email'] = $this->_getSession()->getQuote()->getCustomerEmail();
                        if (empty($this->helper->ifExistingRecord($data))) {
                            $this->messageManager->addErrorMessage(
                                'Product with sku '
                                . $data['sku']
                                . ' requires pre-approval to purchase'
                            );
                            return $this->resultRedirectFactory->create()->setPath(self::REDIRECT_PATH);
                        } else {
                            $resultData = $this->helper->ifExistingRecord($data);
                            if ($currentDate > $resultData['expiry_date']) {
                                $this->messageManager->addErrorMessage('Product requires pre-approval to purchase');
                                return $this->resultRedirectFactory->create()->setPath(self::REDIRECT_PATH);
                            } elseif ($item->getQty() > $resultData['qty']) {
                                $this->messageManager->addErrorMessage('Qty limit exceeded');
                                return $this->resultRedirectFactory->create()->setPath(self::REDIRECT_PATH);
                            }
                        }
                    }
                }
            }

            $subtotal = $this->_getSession()->getQuote()->getSubtotal();
            $customerEmail = $this->_getSession()->getQuote()->getCustomerEmail();
            $storeId = $this->_getSession()->getStoreId();
            $customerGroupId = $this->_getSession()->getQuote()->getCustomerGroupId();
            $this->_getOrderCreateModel()->getQuote()->setCustomerId($this->_getSession()->getCustomerId());
            $this->_processActionData('save');
            $message = $this->restriction->getAdminRestrictionDetails(
                $customerEmail,
                $subtotal,
                $storeId,
                $customerGroupId,
                $this->_getSession()->getQuote()
            );
            if ($message) {
                $this->messageManager->addErrorMessage($message);
                return $this->resultRedirectFactory->create()->setPath('sales/order_create/index', $pathParams);
            }
            $paymentData = $this->getRequest()->getPost('payment');
            if ($paymentData) {
                $paymentData['checks'] = [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_INTERNAL,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
                ];
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();
            if ($order->getId()) {
                $this->updateMetabolicQtyAfterOrder();
            }
            $this->_getSession()->clearStorage();
            $this->messageManager->addSuccessMessage(__('You created the order.'));
            if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
                $pathParams = ['order_id' => $order->getId()];
                $path = self::VIEW_PATH;
            } else {
                $path = self::REDIRECT_PATH;
            }
        } catch (PaymentException $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // customer can be created before place order flow is completed and should be stored in current session
            $this->_getSession()->setCustomerId((int)$this->_getSession()->getQuote()->getCustomerId());
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Order saving error: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath($path, $pathParams);
    }


    public function updateMetabolicQtyAfterOrder()
    {
        if ($this->helper->getModuleEnable()) {
            $items = $this->_getSession()->getQuote()->getAllItems();
            foreach ($this->helper->filterProducts() as $value) {
                $metabolicSkus[] = $value;
            }
            foreach ($items as $item) {
                if (in_array($item->getSku(), $metabolicSkus)) {
                    $data['sku'] = $item->getSku();
                    $data['customer_email'] = $this->_getSession()->getQuote()->getCustomerEmail();
                    $resultData = $this->helper->ifExistingRecord($data);
                    $metabolicData = $this->metabolicModelFactory->create();
                    if (isset($resultData['entity_id'])) {
                        $metabolicData->load($resultData['entity_id']);
                    }
                    $resultData['qty'] = $resultData['qty'] - $item->getQty();
                    $metabolicData->setData($resultData);
                    $metabolicData->save();
                    $request['comment'] = "Order Created for customer "
                        . $data['customer_email']
                        . " with sku "
                        . $data['sku']
                        . " for QTY "
                        . $item->getQty();
                    $request['customer_id'] = $this->_getSession()->getQuote()->getCustomerId();
                    $request['admin_user'] = $this->authSession->getUser()->getUserName();
                    $this->helper->updateComments($request);
                }
            }
        }
    }
}
