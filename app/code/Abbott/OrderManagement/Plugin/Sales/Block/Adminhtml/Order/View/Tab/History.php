<?php

namespace Abbott\OrderManagement\Plugin\Sales\Block\Adminhtml\Order\View\Tab;

class History
{

    /**
     * Compose and get order full history addding the admin username in order comments
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     *
     * @return array
     */
    public function afterGetFullHistory(\Magento\Sales\Block\Adminhtml\Order\View\Tab\History $subject, $result)
    {
        $order = $subject->getOrder();

        $history = [];
        foreach ($order->getAllStatusHistory() as $orderComment) {
            $history[] = $this->prepareHistoryItem(
                $orderComment->getStatusLabel(),
                $orderComment->getIsCustomerNotified(),
                $subject->getOrderAdminDate($orderComment->getCreatedAt()),
                $orderComment->getComment(),
                $orderComment->getAdminUsername()
            );
        }

        foreach ($order->getCreditmemosCollection() as $_memo) {
            $history[] = $this->prepareHistoryItem(
                __('Credit memo #%1 created', $_memo->getIncrementId()),
                $_memo->getEmailSent(),
                $subject->getOrderAdminDate($_memo->getCreatedAt())
            );

            foreach ($_memo->getCommentsCollection() as $_comment) {
                $history[] = $this->prepareHistoryItem(
                    __('Credit memo #%1 comment added', $_memo->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $subject->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getShipmentsCollection() as $_shipment) {
            $history[] = $this->prepareHistoryItem(
                __('Shipment #%1 created', $_shipment->getIncrementId()),
                $_shipment->getEmailSent(),
                $subject->getOrderAdminDate($_shipment->getCreatedAt())
            );

            foreach ($_shipment->getCommentsCollection() as $_comment) {
                $history[] = $this->prepareHistoryItem(
                    __('Shipment #%1 comment added', $_shipment->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $subject->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getInvoiceCollection() as $_invoice) {
            $history[] = $this->prepareHistoryItem(
                __('Invoice #%1 created', $_invoice->getIncrementId()),
                $_invoice->getEmailSent(),
                $subject->getOrderAdminDate($_invoice->getCreatedAt())
            );

            foreach ($_invoice->getCommentsCollection() as $_comment) {
                $history[] = $this->prepareHistoryItem(
                    __('Invoice #%1 comment added', $_invoice->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $subject->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment()
                );
            }
        }

        foreach ($order->getTracksCollection() as $_track) {
            $history[] = $this->prepareHistoryItem(
                __('Tracking number %1 for %2 assigned', $_track->getNumber(), $_track->getTitle()),
                false,
                $subject->getOrderAdminDate($_track->getCreatedAt())
            );
        }

        usort($history, [get_class($subject), 'sortHistoryByTimestamp']);
        return $history;
    }

    /**
     * Map history items as array
     *
     * @param string $label
     * @param bool $notified
     * @param \DateTime $created
     * @param string $comment
     * @param string $adminUsername
     * @return array
     */
    protected function prepareHistoryItem($label, $notified, $created, $comment = '', $adminUsername = '')
    {
        return ['title' => $label,
            'notified' => $notified,
            'comment' => $comment,
            'created_at' => $created,
            'admin_username' => $adminUsername
        ];
    }
}
