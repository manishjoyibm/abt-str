<?php
namespace Abbott\Sarp2\Plugin\Engine\Notification;

use Aheadworks\Sarp2\Engine\Notification\DataResolver as AwDataResolver;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Model\Email\Template\PriceFormatter;

class DataResolverPlugin
{
    private PriceFormatter $priceFormatter;

    public function __construct(
        PriceFormatter $priceFormatter
    ) {
        $this->priceFormatter = $priceFormatter;
    }

    public function afterResolve(
        AwDataResolver $subject,
        array $result,
        ResolveSubject $resolveSubject
    ): array {
        $sourcePayment = $resolveSubject->getSourcePayment();
        $profile = $sourcePayment->getProfile();
        $subtotal = 0.0;

        foreach ($profile->getItems() as $item) {
            $qty          = (float) $item->getData('qty');
            $regularPrice = (float) $item->getData('regular_price');
            $subtotal += ($qty * $regularPrice);
        }
        $currencyCode = (string) $profile->getProfileCurrencyCode();
        $result['finalPrice'] = $this->priceFormatter->format($subtotal, $currencyCode);
        return $result;
    }
}