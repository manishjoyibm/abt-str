<?php

namespace Abbott\OrderManagement\Model\Order\Pdf;

use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

class Invoice extends \Amasty\Orderattr\Model\Order\Pdf\Invoice
{
    public $_localeResolver;
    const LINES = 'lines';

    const SPORTS = '4';
    
    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->_localeResolver->emulate($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $page = $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->_localeResolver->revert();
            }
            
            if ($order->getCustomerGroupId() == self::SPORTS) {
                $page = $this->insertBottomAddress($page, $order->getCustomerGroupId(), $invoice->getStore());
                $page = $this->insertinvoiceComments($page, $invoice);
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }
    
    /**
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Zend_Pdf_Page $page
     */
    public function insertinvoiceComments(\Zend_Pdf_Page $page, $invoice)
    {
        $this->_setFontBold($page, 10);
        $lineBlock = [self::LINES => [], 'height' => 15];
        $lineBlock[self::LINES][] = $this->getLineBlock("NOTES:", 'bold');
        $this->y -= 10;
        $this->_setFontRegular($page, 10);
        foreach ($invoice->getComments() as $comment) {
            $textChunk = wordwrap(strip_tags($comment->getComment()), 120, "\n");
            foreach (explode("\n", $textChunk) as $textLine) {
                if ($textLine!=='') {
                    $lineBlock[self::LINES][] = $this->getLineBlock(trim(strip_tags($textLine)), 'regular');
                }
            }
        }
        return $this->drawLineBlocks($page, [$lineBlock]);
    }
    
    /**
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return \Zend_Pdf_Page $page
     */
    public function insertBottomAddress(\Zend_Pdf_Page $page, $customerGroupId, $store = null)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->_setFontBold($page, 10);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        if ($customerGroupId == self::SPORTS) {
            $values = explode(
                "\n",
                $this->_scopeConfig->getValue(
                    'sales/identity/sports_address',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                )
            );
        } else {
            $values = explode(
                "\n",
                $this->_scopeConfig->getValue(
                    'sales/identity/address',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                )
            );
        }
        $lineBlock = [self::LINES => [], 'height' => 10];
        $lineBlock[self::LINES][] = $this->getLineBlock("Please Remit payment to:", 'bold');
        foreach ($values as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $lineBlock[self::LINES][] = $this->getLineBlock(trim(strip_tags($_value)), 'bold');
                }
            }
        }
        $page = $this->drawLineBlocks($page, [$lineBlock]);
        $note = $this->_scopeConfig->getValue(
            'sales/identity/address_note',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        $this->_setFontRegular($page, 10);
        $this->y = $this->y-10;
        $lineBlock[self::LINES] = [];
        $lineBlock[self::LINES][] = $this->getLineBlock($note, 'regular');
        return $this->drawLineBlocks($page, [$lineBlock]);
    }
    
    /**
     * @param string $text
     * @param string $font
     * @return array
     */
    public function getLineBlock($text, $font)
    {
        return [
            [
                'text' => $text,
                'feed' => 35,
                'font_size' => 10,
                'font' => $font,
            ]
        ];
    }
}
