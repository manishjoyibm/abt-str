<?php
namespace Abbott\PedialyteCart\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Abbott\PedialyteCart\Helper\Data as PedialyteCartHelper;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var PedialyteCartHelper
     */
    private PedialyteCartHelper $pedialyteCartHelper;

    /**
     * @param PedialyteCartHelper $pedialyteCartHelper
     */
    public function __construct(PedialyteCartHelper $pedialyteCartHelper)
    {
        $this->pedialyteCartHelper = $pedialyteCartHelper;
    }


    /**
     * @return array
     */
    public function getConfig(): array
    {
        $pdlDiscount_enable = $this->pedialyteCartHelper->isDiscountPriceEnable();
        $pdlDiscountdiscount_label = $this->pedialyteCartHelper->getDiscountLabel();
        $isShowShippingProgressBar = $this->pedialyteCartHelper->isShowShippingProgressBar();
        return [
            'isShowShippingProgressBar' => (int)$isShowShippingProgressBar,
            'isPdlDiscountEnable' => $pdlDiscount_enable,
            'pdlDiscountLabel' => $pdlDiscountdiscount_label
        ];
    }
}
