<?php
namespace Abbott\ShoppingCart\Controller\Index;

use Abbott\ShoppingCart\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    public $resultJsonFactory;
    protected $helper;

    /**
     * Construct function
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $shippingData = $this->helper->getShippingDetails();
        return $resultJson->setData([
            'html' => $shippingData[0]['message'],
            'color' => $shippingData[0]['color'],
            'percentage' => $shippingData[0]['percentage']
        ]);
    }
}
