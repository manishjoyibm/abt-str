<?php

namespace Abbott\OrderManagement\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Framework\DataObject;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    protected function prepareTemplate(Order $order)
    {
         $orderStatus = ($order->getStatusLabel() == 'Suspected Fraud') ? 'Processing' : $order->getStatusLabel();
         $isBackOrdered = (strcasecmp(trim($orderStatus), 'BackOrdered') === 0);

         $transport = [
            'order' => $order,
            'order_id' => $order->getId(),
            'order_grand_total' => number_format($order->getGrandTotal(), 2),
            'custom_order_status' => $orderStatus,
            'is_backordered'      => $isBackOrdered,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'created_at_formatted' => $order->getCreatedAtFormatted(2),
            'created_at_formatted_1'=>$order->getCreatedAtFormatted(1),
            'order_data' => [
                'customer_name' => $order->getCustomerName(),
                'is_not_virtual' => $order->getIsNotVirtual(),
                'email_customer_note' => $order->getEmailCustomerNote(),
                'frontend_status_label' => $order->getFrontendStatusLabel(),
                'payment_title' => $order->getPayment()->getMethodInstance()->getTitle(),
                'payment_additional_info' =>$order->getPayment()->getAdditionalInformation('cc_number'),
                'billing_email' => $order->getBillingAddress()->getEmail()
            ]
         ];
         $statusHtml = $isBackOrdered ? '<td style="font-family: Georgia, Arial, sans-serif, serif, EmojiFont; color:red; font-weight:bold;">' . htmlspecialchars($orderStatus) . '</td>'
            : '<td style="font-family: Georgia, Arial, sans-serif, serif, EmojiFont;">' . htmlspecialchars($orderStatus) . '</td>';

         $transport['custom_order_status_td_html'] = $statusHtml;
         $transportObject = new DataObject($transport);

        /**
         * Event argument `transport` is @deprecated. Use `transportObject` instead.
         */
         $this->eventManager->dispatch(
             'email_order_set_template_vars_before',
             ['sender' => $this, 'transport' => $transportObject, 'transportObject' => $transportObject]
         );

        $this->templateContainer->setTemplateVars($transportObject->getData());

        Sender::prepareTemplate($order);
    }
}
