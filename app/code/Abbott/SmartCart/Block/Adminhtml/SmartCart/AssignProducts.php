<?php

namespace Abbott\SmartCart\Block\Adminhtml\SmartCart;

use Abbott\SmartCart\Model\SmartCart;
use Abbott\SmartCart\Model\SmartCartFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Category\Tab\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;

class AssignProducts extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'products/assign_products.phtml';
    /**
     * @var Product
     */
    protected $blockGrid;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var SmartCartFactory
     */
    private $smartCartFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param EncoderInterface $jsonEncoder
     * @param SmartCartFactory $smartCartFactory
     * @param CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EncoderInterface $jsonEncoder,
        SmartCartFactory $smartCartFactory,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->smartCartFactory = $smartCartFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Abbott\SmartCart\Block\Adminhtml\SmartCart\Tab\Productgrid',
                'smartcart.product.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     * @throws LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * GetProductsJson
     *
     * @return string
     */
    public function getProductsJson()
    {
        $entityId = $this->getRequest()->getParam('id');
        if ($entityId) {
            $smartCart = $this->getSmartCart();
            if ($products = $smartCart->getProducts()) {
                return $this->jsonEncoder->encode($products);
            }
        }
        return '{}';
    }

    /**
     * GetSmartCart
     *
     * @return mixed|null
     */
    public function getSmartCart()
    {
        return $this->registry->registry('smartcart');
    }
}
