<?php

namespace Abbott\MyAccount\Plugin\Controller\Catalog\Category;

use Abbott\CustomerTransistion\Helper\Data;
use Magento\Catalog\Controller\Category\View;
use Magento\Framework\App\Response\Http;

class ViewPlugin
{
    public $helper;
    public $response;
    /**
     * Construct function
     *
     * @param Data $helper
     * @param Http $response
     */
    public function __construct(
        Data $helper,
        Http $response
    ) {
          $this->helper = $helper;
          $this->response = $response;
    }

    /**
     * BeforeExecute function
     *
     * @param View $subject
     */
    public function beforeExecute(View $subject)
    {
        $this->response->setRedirect($this->helper->getFailureUrl().'pagenotfound');
    }
}
