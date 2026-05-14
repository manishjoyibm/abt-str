<?php
namespace Abbott\RestrictCheckout\Block\Onepage;

class Link extends \Magento\Checkout\Block\Onepage\Link
{
    public $sgpRestriction;
    public $messageManager;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Abbott\RestrictCheckout\Model\Restriction $sgpRestriction
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Abbott\RestrictCheckout\Model\Restriction $sgpRestriction,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->sgpRestriction = $sgpRestriction;
        $this->messageManager = $messageManager;
        parent::__construct($context, $checkoutSession, $checkoutHelper, $data);
    }

    public function isSgpRestricted()
    {
      if ($this->sgpRestriction->validateCustomerGroup()) {
        $orderTotal = (double)$this->sgpRestriction->getOrderTotalForCustomer();
        $orderLimit = (double)$this->sgpRestriction->getOrderLimit();
        $quoteTotal = (double)$this->_checkoutSession->getQuote()->getSubtotalWithDiscount();
        if (($orderTotal + $quoteTotal) > $orderLimit) {
          $this->messageManager->addError(
              $this->sgpRestriction->getMessage()
          );
          return true;
        }
      }
      return false;
    }
}
