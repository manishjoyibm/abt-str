<?php
namespace Abbott\AdultSignature\Plugin\Sales\Controller\Adminhtml\Order\Create;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Controller\Adminhtml\Order\Create\Save as Subject;
use Magento\Backend\Model\Session\Quote as BackendQuoteSession;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\App\RequestInterface;

class SavePlugin
{
    private BackendQuoteSession $sessionQuote;
    private RedirectFactory $resultRedirectFactory;
    private MessageManager $messageManager;
    private RequestInterface $request;

    public function __construct(
        BackendQuoteSession $sessionQuote,
        RedirectFactory     $resultRedirectFactory,
        MessageManager      $messageManager,
        RequestInterface    $request
    ) {
        $this->sessionQuote          = $sessionQuote;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager        = $messageManager;
        $this->request               = $request;
    }

    public function aroundExecute(Subject $subject, \Closure $proceed)
    {
        try {
            // Validate BEFORE saving
            $orderData  = (array)$this->request->getParam('order', []);
            $ackChecked = ((int)($orderData['abbott_adult_signature_ack'] ?? 0) === 1);

            $quote = $this->sessionQuote->getQuote();
            if ($this->isAdultSigRequiredForQuote($quote) && !$ackChecked) {
                throw new LocalizedException(
                    __('Adult Signature is required for this order. Please confirm by checking the Adult Signature box.')
                );
            }

            // Proceed with normal save
            return $proceed();

        } catch (LocalizedException $e) {
            // Show the error and redirect back to the SAME create-order screen
            $this->messageManager->addErrorMessage($e->getMessage());

            $customerId = (int)$this->sessionQuote->getCustomerId();
            $storeId    = (int)$this->sessionQuote->getStoreId();

            $resultRedirect = $this->resultRedirectFactory->create();
            // This keeps the current context (customer/store) so you stay on the same page
            $resultRedirect->setPath(
                'sales/order_create/index',
                ['customer_id' => $customerId, 'store_id' => $storeId]
            );
            return $resultRedirect;

        } catch (\Throwable $e) {
            // Fallback: generic message, then same redirect
            $this->messageManager->addErrorMessage(__('Unable to place order. Please try again.'));
            $customerId = (int)$this->sessionQuote->getCustomerId();
            $storeId    = (int)$this->sessionQuote->getStoreId();

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(
                'sales/order_create/index',
                ['customer_id' => $customerId, 'store_id' => $storeId]
            );
            return $resultRedirect;
        }
    }

    private function isAdultSigRequiredForQuote(\Magento\Quote\Model\Quote $quote = null): bool
    {
        if (!$quote || $quote->isVirtual()) 
            {
                return false;
            }

        $addr     = $quote->getShippingAddress();
        $regionId = $addr ? (int)$addr->getRegionId() : 0;
        if ($regionId <= 0)
            {
                 return false;
            }

        foreach ($quote->getAllVisibleItems() as $item) {
            $p = $item->getProduct();
            $requires = (int)$p->getData('abbott_requires_adult_signature') === 1;
            if (!$requires) 
                {
                    continue;
                }

            $csv = (string)$p->getData('abbott_shipping_state_adult_signature');
            $ids = array_filter(array_map('intval', explode(',', $csv)));

            if (empty($ids) || in_array($regionId, $ids, true)) {
                return true;
            }
        }
        return false;
    }
}