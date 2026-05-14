<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Block\Order\Plugin;

use Amasty\Orderattr\Block\Order\Attributes as OrderAttributes;
use Amasty\Orderattr\Model\ConfigProvider;
use Amasty\Orderattr\Model\OrderEmail\IsActionRelatedPdfAction;
use Magento\Sales\Block\Items\AbstractItems;
use Magento\Sales\Block\Order\Email\Invoice\Items as InvoiceItems;
use Magento\Sales\Block\Order\Email\Shipment\Items as ShipmentItems;

class OrderEmail
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsActionRelatedPdfAction
     */
    private $isActionRelatedPdfAction;

    public function __construct(
        ConfigProvider $configProvider,
        IsActionRelatedPdfAction $isActionRelatedPdfAction
    ) {
        $this->configProvider = $configProvider;
        $this->isActionRelatedPdfAction = $isActionRelatedPdfAction;
    }

    /**
     * @param AbstractItems $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(AbstractItems $subject, string $result): string
    {
        $isPrintAllowed = true;

        if ($this->isActionRelatedPdfAction->execute()) {
            if ($subject instanceof InvoiceItems) {
                $isPrintAllowed = $this->configProvider->isIncludeToInvoicePdf();
            } elseif ($subject instanceof ShipmentItems) {
                $isPrintAllowed = $this->configProvider->isIncludeToShipmentPdf();
            }
        }

        /** @var OrderAttributes $attributesBlock */
        if ($isPrintAllowed
            && $attributesBlock = $subject->getChildBlock('order_attributes')
        ) {
            $result .= $attributesBlock->toHtml();
        }

        return $result;
    }
}
