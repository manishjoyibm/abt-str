<?php

declare(strict_types=1);

namespace Abbott\MyAccount\Plugin\Controller\Cms;

use Abbott\CustomerTransistion\Helper\Data;
use Magento\Cms\Controller\Index\Index;
use Magento\Framework\App\Response\Http;

class IndexPlugin
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
     * @param Index $subject
     * @return void
     */
    public function beforeExecute(Index $subject)
    {
        $this->response->setRedirect($this->helper->getFailureUrl());
    }
}
