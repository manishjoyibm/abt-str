<?php

declare(strict_types=1);

namespace Abbott\AdditionalAttributes\Model\Resolver\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

/**
 * @inheritdoc
 */
class Qty implements ResolverInterface
{
    const MODEL = 'model';
    const BACKORDER = 'backorder';
    const BRAND = 'Metabolics';
    const AVAILABLE_FOR_CALL = 1;
    const LEVEL = 'Level1';
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    protected $metabolicFactory;

    protected $customerSession;

    protected $timezoneInterface;

    protected $metabolicData;

    /**
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        StockStatusRepositoryInterface $stockStatusRepository,
        MetabolicFactory $metabolicFactory,
        CustomerSession $customerSession,
        TimezoneInterface $timezoneInterface,
        MetabolicData $metabolicData
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->metabolicFactory = $metabolicFactory;
        $this->customerSession = $customerSession;
        $this->timezoneInterface = $timezoneInterface;
        $this->metabolicData = $metabolicData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!array_key_exists(self::MODEL, $value) || !$value[self::MODEL] instanceof ProductInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var $product ProductInterface */
        $product = $value[self::MODEL];

        $stockdata['qty'] = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getQty();

        $stockdata['custom_order_on_call'] = $product->getOrderOnCall();
        $customerEmailID = $this->customerSession->getCustomer()->getEmail();
        if (($this->metabolicData->getLevelAttributeLabel($product->getSku()) == self::LEVEL)
        && ($customerEmailID != null)
        && ($product->getOrderOnCall() == self::AVAILABLE_FOR_CALL)) {
            $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
            $data['sku'] = $product->getSku();
            $data['customer_email'] = $customerEmailID;
            if ($this->metabolicData->ifExistingRecord($data)) {
                $metabolicDataResult = $this->metabolicData->ifExistingRecord($data);
                if (($product->getSku() == $metabolicDataResult['sku'])
                && ($metabolicDataResult['expiry_date'] >= $currentDate)
                && ($metabolicDataResult['qty'] > 0)) {
                    $metabolicDataResult['qty'] = ($stockdata['qty'] < $metabolicDataResult['qty'])
                    ? $stockdata['qty'] : $metabolicDataResult['qty'];
                    $stockdata['qty'] = $metabolicDataResult['qty'];
                    $stockdata['custom_order_on_call'] = 0;
                }
            }
        }

        $stockdata[self::BACKORDER] = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getBackorders();
        $stockStatus = $this->stockStatusRepository->get($product->getId());
        $productStockStatus = (int)$stockStatus->getStockStatus();
        if (($stockdata[self::BACKORDER] == 1 || $stockdata[self::BACKORDER] == 2)
        && $productStockStatus == 1 && $stockdata['qty'] <= 0) {
            $stockdata['status'] = "BACK ORDER";
        } else {
            $stockdata['status'] = $productStockStatus === 1 ? 'IN_STOCK' : 'OUT_OF_STOCK';
        }
        return $stockdata;
    }
}
