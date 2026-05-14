<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Plugin\Checkout;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Abbott\AdultSignature\Model\Service\AdultSignatureEvaluator;

class AfterSaveShippingInformationPlugin
{
    public function __construct(
        private CartRepositoryInterface $quoteRepository,
        private AdultSignatureEvaluator $evaluator
    ) {}

    public function afterSaveAddressInformation(
        ShippingInformationManagement $subject,
        $result,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        $res = $this->evaluator->evaluate($quote);

        $quote->setData('adult_signature_required', (int)$res['required']);
        if (!$res['required']) {
            $quote->setData('adult_signature_accepted', 0);
        } else {
            $quote->setData('adult_signature_accepted', 1);
        }
        $this->quoteRepository->save($quote);
        return $result;
    }
}