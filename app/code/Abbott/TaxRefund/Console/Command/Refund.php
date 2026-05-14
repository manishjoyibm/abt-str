<?php
namespace Abbott\TaxRefund\Console\Command;

use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Refund extends Command
{
    const COMMENT_TEXT = "comment_text";

    const SEND_EMAIL = "send_email";
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    protected $state;
    protected $directoryList;
    protected $csvProcessor;
    protected $orderRepository;
    protected $date;
    protected $creditmemoManagement;

    /**
     * @param \Abbott\TaxRefund\Controller\Index\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     */
    public function __construct(
        \Abbott\TaxRefund\Controller\Index\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->state = $state;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->orderRepository = $orderRepository;
        $this->date = $date;
        $this->creditmemoManagement = $creditmemoManagement;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('abbott:refund')
            ->setDescription('Refunding the Extra Charged Amount');
        return parent::configure();
    }

    /**
     * @param InputInterface $inp
     * @param OutputInterface $out
     * @return int|void|null
     */
    public function execute(InputInterface $inp, OutputInterface $out)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $path = $this->directoryList->getPath('var') . '/Abbott/RefundRequest/refund-orders.csv';
        $csvData = $this->csvProcessor->getData($path);
        $result[] = ['Entity Id','Order Id','Message'];
        array_shift($csvData);
        foreach ($csvData as $rowData) {
            try {
                $orderId = $rowData[0];
                $order = $this->orderRepository->get($orderId);
                if ($order->getStatus() == 'complete') {
                    $invoice = null;
                    if (isset($rowData[5])) {
                        $invoice = $this->getGivenInvoiceForOrder($order, $rowData[5]);
                        if (!$invoice) {
                            $out->writeln(
                                "The given invoice is not linked with order ".
                                $order->getIncrementId()." or an unpaid invoice"
                            );
                        }
                    } else {
                        $invoice = $this->getPaidInvoiceForOrder($order);

                    }
                    if ($invoice && count($this->getItemsArray($invoice))) {
                        $items = $this->getItemsArray($invoice);
                        $data = [
                                            "items"=> $items,
                                            "do_offline"=> 0,
                                            self::COMMENT_TEXT=> isset($rowData[3]) ? $rowData[3] : '',
                                            "shipping_amount"=> 0,
                                            "adjustment_positive"=> $rowData[2],
                                            "adjustment_negative"=>0,
                                            "refund_customerbalance_return_enable"=>0,
                                            self::SEND_EMAIL => isset($rowData[4]) ? $rowData[4] : 0
                            ];
                        $invoiceId = $invoice->getEntityId();
                        $this->creditmemoLoader->setOrderId($orderId);
                        $this->creditmemoLoader->setCreditmemo($data);
                        $this->creditmemoLoader->setInvoiceId($invoiceId);
                        $creditmemo = $this->creditmemoLoader->load();
                        if (!empty($data[self::COMMENT_TEXT])) {
                            $creditmemo->addComment(
                                $data[self::COMMENT_TEXT]
                            );
                            $creditmemo->setCustomerNote($data[self::COMMENT_TEXT]);
                        }
                        $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data[self::SEND_EMAIL]));
                        $this->creditmemoManagement->refund($creditmemo, (bool)$data[self::SEND_EMAIL]);
                        if (!empty($data[self::SEND_EMAIL]) && $data[self::SEND_EMAIL] == 1) {
                            $this->creditmemoSender->send($creditmemo);
                        }
                        $out->writeln('Amount Credited for ' . $order->getIncrementId());
                        $result[] = [$orderId, $order->getIncrementId(), 'Credit memo generated'];
                    } else {
                        $out->writeln($order->getIncrementId() . ' has no invoices');
                        $result[] = [$orderId, $order->getIncrementId(), 'Order has no invoices'];
                    }
                } else {
                    $out->writeln($order->getIncrementId() . ' is not completed');
                    $result[] = [$orderId, $order->getIncrementId(), 'Order is not completed'];
                }
            } catch (\Exception $e) {
                $out->writeln('Exception for order ' . $order->getIncrementId());
                $out->writeln($e->getMessage());
                $result[] = [$orderId, $rowData[1], $e->getMessage()];
            }
        }
        $fileName = 'refundresult_' . $this->date->timestamp() . '.csv';
        $filePath = $this->directoryList->getPath('var') . '/Abbott/RefundGeneration/' . $fileName;
        $this->csvProcessor->setDelimiter(',')->setEnclosure('"')->saveData($filePath, $result);
        $out->writeln('File is generated at ' . $filePath);
    }

    /**
     * @param /Magento/Sales/Model/Order $order
     * @return /Magento/Sales/Model/Order/Invoice|null
     */
    private function getPaidInvoiceForOrder($order)
    {
        $invoice = null;
        foreach ($order->getInvoiceCollection() as $inv) {
            if ($inv->getState() == 2) {
                return $inv;
            }
        }
        return $invoice;
    }

    /**
     * @param /Magento/Sales/Model/Order $order
     * @param int $invId
     * @return /Magento/Sales/Model/Order/Invoice|null
     */
    private function getGivenInvoiceForOrder($order, $invId)
    {
        $invoice = null;
        foreach ($order->getInvoiceCollection() as $inv) {
            if ($inv->getEntityId() == $invId && $inv->getState() == 2) {
                return $inv;
            }
        }
        return $invoice;
    }

    /**
     * @param /Magento/Sales/Model/Order/Invoice $invoice
     * @return array
     */
    private function getItemsArray($invoice)
    {
        $items = [];
        foreach ($invoice->getAllItems() as $item) {
            $orderItemId = $item->getOrderItemId();
            $items[$orderItemId] = ["qty" => 0 ];
        }
        return $items;
    }
}
