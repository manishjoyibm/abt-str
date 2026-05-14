<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Abbott\AdultSignature\Model\Service\AdultSignatureEvaluator;
use Magento\Quote\Model\QuoteAddress;
use Psr\Log\LoggerInterface;

/**
 * Observer that evaluates adult signature flags during totals phases.
 * Works whether the event carries 'quote_address', 'address', or just 'quote'.
 */
class ApplyAdultSignatureOnTotals implements ObserverInterface
{
    public function __construct(
        private AdultSignatureEvaluator $evaluator,
        private LoggerInterface $logger
    ) {}

    public function execute(Observer $observer): void
    {
        // Try the several common event payloads
        $address = $observer->getEvent()->getQuoteAddress() ?: $observer->getEvent()->getAddress();
        if ($address instanceof QuoteAddress && $address->getQuote()) {
            $quote = $address->getQuote();
        } else {
            $quote = $observer->getEvent()->getQuote();
        }

        if (!$quote) {
            $this->logger->debug('[AdultSignature] Observer fired but no quote/address available.');
            return;
        }

        $result = $this->evaluator->evaluate($quote);

        $quote->setData('adult_signature_required', (int)$result['required']);

        if (!$result['required']) {
            $quote->setData('adult_signature_accepted', 0);
        } else {
            $quote->setData('adult_signature_accepted', 1);
        }

        $this->logger->debug('[AdultSignature] Observer set required=' . (int)$result['required']);
    }
}