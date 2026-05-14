<?php

namespace Abbott\MyAccount\Model\Config\Source;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\View\Element\Html\Links;

class Accountlink implements ArrayInterface
{
    /** @var Files  */
    protected $utilityFiles;

    protected $links;

    protected $list = [];

    /**
     * Construct function
     *
     * @param Files $utilityFiles
     * @param Links $links
     */
    public function __construct(
        Files $utilityFiles,
        Links $links
    ) {
        $this->utilityFiles = $utilityFiles;
        $this->links = $links;
    }

    /**
     * ToOptionArray function
     *
     * @return array
     */
    public function toOptionArray()
    {
         return [
            [
                'value' => 'customer-account-navigation-account-link',
                'label' => __('My Account')
            ],
              [
                'value' => 'customer-account-navigation-downloadable-products-link',
                'label' => __('My Downlodable Products')
            ],
              [
                'value' => 'customer-account-navigation-customer-balance-link',
                'label' => __('Store Credit')
            ],
             [
                 'value' => 'customer-account-navigation-gift-card-link',
                'label' => __('Gift Card')
                 ]
         ];
    }
}
