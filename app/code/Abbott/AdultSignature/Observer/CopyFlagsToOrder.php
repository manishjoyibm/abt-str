<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer to copy adult signature flags from quote to order and enforce acceptance.
 *
 * @category  Abbott
 * @package   Abbott_AdultSignature
 */
class CopyFlagsToOrder implements ObserverInterface
{
    /**
     * Execute observer logic: block order if required and not accepted, and copy fields to order.
     *
     * @param Observer $observer Event observer
     * @return void
     * @throws LocalizedException If acceptance is required but missing
     */
    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $required = (int)$quote->getData('adult_signature_required');
        $accepted = (int)$quote->getData('adult_signature_accepted');

        if ($required && !$accepted) {
            throw new LocalizedException(__('Adult Signature requirement must be accepted before placing the order.'));
        }

        $order->setData('adult_signature_required', $required);
        $order->setData('adult_signature_accepted', $accepted);
    }
}
