<?php
namespace Abbott\OrderStatus\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Config;

class Adminpayments extends DataObject implements OptionSourceInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $appConfigScopeConfigInterface;
    /**
     * @var Config
     */
    protected Config $paymentModelConfig;

    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config $paymentModelConfig
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig
    ) {
        $this->appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->paymentModelConfig = $paymentModelConfig;
    }

    /**
     * List of all payment methods
     */
    public function toOptionArray(): array
    {
         $payments = $this->paymentModelConfig->getMethodsInfo();
         $methods = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->appConfigScopeConfigInterface
            ->getValue('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = [
            'label' => $paymentTitle,
            'value' => $paymentCode
            ];
        }
        return $methods;
    }
}
