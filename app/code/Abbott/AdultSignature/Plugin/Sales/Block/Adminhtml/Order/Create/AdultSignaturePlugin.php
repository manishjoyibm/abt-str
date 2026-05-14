<?php
namespace Abbott\AdultSignature\Plugin\Sales\Block\Adminhtml\Order\Create;

use Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form as Subject;

class AdultSignaturePlugin 
{
    public function afterToHtml(Subject $subject, string $result): string
    {
        $extra = $subject->getLayout()
            ->createBlock(\Abbott\AdultSignature\Block\Adminhtml\Order\Create\AdultSignature::class)
            ->setTemplate('Abbott_AdultSignature::order/create/adult_signature.phtml')
            ->toHtml();

        return $result . $extra;
    }
}