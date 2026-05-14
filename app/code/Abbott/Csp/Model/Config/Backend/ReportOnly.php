<?php
namespace Abbott\Csp\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class ReportOnly extends Value
{
    /**
     * @var WriterInterface
     */
    protected WriterInterface $configWriter;

    /**
     * @param WriterInterface $configWriter
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        WriterInterface      $configWriter,
        Context              $context,
        Registry             $registry,
        ScopeConfigInterface $scopeConfig,
        TypeListInterface    $cacheTypeList,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null,
        array                $data = []
    ) {
        $this->configWriter = $configWriter;
        parent::__construct($context, $registry, $scopeConfig, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After Save
     *
     * @return ReportOnly
     */
    public function afterSave()
    {
        $value = $this->getValue();
        $this->configWriter->save('csp/mode/storefront/report_only', $value);
        $this->configWriter->save('csp/mode/admin/report_only', $value);
        $this->configWriter->save('csp/mode/storefront_checkout_index_index/report_only', $value);
        $this->configWriter->save('csp/mode/admin_sales_order_create_index/report_only', $value);
        $this->configWriter->save('csp/mode/dashboard_index_index/report_only', $value);
        return parent::afterSave();
    }
}
