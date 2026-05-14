<?php


namespace Abbott\Catalog\Plugin\Catalog\Model;

use Abbott\Catalog\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ProductPlugin
{

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var Data
     */
    private Data $helper;

    /**
     * ProductSearchPlugin constructor.
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     */
    public function __construct(StoreManagerInterface $storeManager, Data $helper)
    {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    public function afterIsSalable(Product $subject, $result): mixed
    {
        return $this->checkIsDisableSaleEnabled($subject, $result);
    }

    /**
     * @param Product $subject
     * @param $result
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    public function afterGetIsSalable(Product $subject, $result): mixed
    {
        return $this->checkIsDisableSaleEnabled($subject, $result);
    }

    /**
     * @param $subject
     * @param $result
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    protected function checkIsDisableSaleEnabled($subject, $result): mixed
    {
        if ($this->helper->isDisableSaleEnabled($this->storeManager->getStore()->getId()) &&
            $subject->getData("disable_sale")
        ) {
            $result = false;
        }
        return $result;
    }
}
