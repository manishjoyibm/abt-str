<?php
namespace Abbott\ShoppingCart\ViewModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Abbott\ShoppingCart\Helper\Data as dataHelper;

class Shipping implements ArgumentInterface
{
     /**
      * @var DataHelper
      */
    protected $dataHelper;

    /**
     * Construct
     *
     * @param dataHelper $dataHelper
     */
    public function __construct(
        DataHelper $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * GetShippingDetails
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getShippingDetails()
    {
        return $this->dataHelper->getShippingDetails();
    }

    /**
     * GetShippingAmount
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getShippingAmount()
    {
        return $this->dataHelper->getFreeShippingAmount();
    }
}
