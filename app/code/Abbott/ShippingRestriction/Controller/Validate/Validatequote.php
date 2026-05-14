<?php

namespace Abbott\ShippingRestriction\Controller\Validate;

use Abbott\ShippingRestriction\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Validatequote extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;

    protected $shippRestrictionHelper;

    protected $resultPageFactory;

    /**
     * Construct function
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $shippRestrictionHelper
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $shippRestrictionHelper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct(
            $context
        );
        $this->resultJsonFactory = $resultJsonFactory;
        $this->shippRestrictionHelper = $shippRestrictionHelper;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute function
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $output = null;
        try {
            $post = $this->getRequest()->getContent();
            $dataobj = json_decode($post);
            $region_id = isset($dataobj->regionId) ? $dataobj->regionId : null;
            $streetArray = isset($dataobj->street) ? $dataobj->street : [];
            $isEnabled = $this->shippRestrictionHelper->isEnabled();
            $pobox = $this->shippRestrictionHelper->validateStreet($streetArray);
            if ($pobox) {
                $output = $resultPage->getLayout()
                    ->createBlock('Magento\Framework\View\Element\Template')
                    ->setTemplate('Abbott_ShippingRestriction::po-box-error.phtml')
                    ->toHtml();
                $resultJson->setData($output);
                return $resultJson;
            }
            if ($isEnabled && $region_id) {
                $output = $resultPage->getLayout()
                    ->createBlock('Abbott\ShippingRestriction\Block\Region')
                    ->setTemplate('Abbott_ShippingRestriction::region-error.phtml')
                    ->setData('region_id', $region_id)
                    ->toHtml();
                $resultJson->setData($output);
                return $resultJson;
            }

        } catch (\Exception $ex) {
            $default = $ex->getMessage();
            $resultJson->setData($default);
        }
        return $resultJson;
    }
}
