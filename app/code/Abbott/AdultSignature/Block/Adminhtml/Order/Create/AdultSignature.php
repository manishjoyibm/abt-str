<?php
namespace Abbott\AdultSignature\Block\Adminhtml\Order\Create;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Session\Quote as BackendQuoteSession;
use Abbott\AdultSignature\Model\Config;


class AdultSignature extends Template
{
    private BackendQuoteSession $sessionQuote;
    private Config $config;

    public function __construct(
        Template\Context $context,
        BackendQuoteSession $sessionQuote,
        Config $config, 
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sessionQuote = $sessionQuote;
        $this->config = $config;
    }

    public function isAdultSignatureRequiredForQuote(): bool
    {
        $quote = $this->sessionQuote->getQuote();
        if (!$quote || $quote->isVirtual()) {
            return false;
        }

        $addr = $quote->getShippingAddress();
        $regionId = $addr ? (int)$addr->getRegionId() : 0;
        if ($regionId <= 0) {
            return false; // no region yet -> don’t force checkbox
        }

        foreach ($quote->getAllVisibleItems() as $item) {
            $p = $item->getProduct();
            $requires = (int)$p->getData('abbott_requires_adult_signature') === 1;
            if (!$requires) {
                continue;
            }
            $csv = (string)$p->getData('abbott_shipping_state_adult_signature');
            $ids = array_filter(array_map('intval', explode(',', $csv)));

            // If no states selected but product requires adult signature -> treat as required for all states
            if (empty($ids) || in_array($regionId, $ids, true)) {
                return true;
            }
        }
        return false;
    }
    
    public function getAdminMessage(): string
        {
            return $this->config->getAdminMessage();
        }


    public function getFieldName(): string { return 'order[abbott_adult_signature_ack]'; }
    public function getFieldId(): string   { return 'abbott_adult_signature_ack'; }
    

}
