<?php
namespace Abbott\Breadcrumb\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\View\LayoutFactory;
use \Magento\Framework\App\RequestInterface;

class Data extends AbstractHelper
{
    /**
     * @var object
     */
    protected $layoutFactory;
    /**
     * @var object
     */
    protected $request;

    /**
     * @var object
     */
    protected $accountHelper;

    /**
     * @var object
     */
    protected $custometransHelper;

    /**
     * @param LayoutFactory $layoutFactory
     * @param RequestInterface $requestInterface
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        RequestInterface $requestInterface,
        \Abbott\MyAccount\Helper\Data $accountHelper,
        \Abbott\CustomerTransistion\Helper\Data $custometransHelper
    ) {
         $this->layoutFactory = $layoutFactory;
         $this->request = $requestInterface;
         $this->accountHelper = $accountHelper;
         $this->custometransHelper = $custometransHelper;
    }

    /**
     * @return string
     */
    public function getLastCruminfo()
    {
        $retrunVal = '';
        if ($this->request->getParam('order_id')) {

            $salesblockObj= $this->layoutFactory->create()->createBlock(\Magento\Sales\Block\Order\View::class);

            if ($salesblockObj->getOrder()) {

                $retrunVal = $salesblockObj->getOrder()->getIncrementId();
            }

        } elseif ($this->request->getParam('profile_id')) {
            $subscriptionblockObj = $this->layoutFactory->create()
                ->createBlock('Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View');
            if ($subscriptionblockObj->getProfile()) {
                $retrunVal = $subscriptionblockObj->getProfile()->getIncrementId();
            }
        } elseif ($this->request->getParam('entity_id')) {

            $rmablockObj= $this->layoutFactory->create()->createBlock(\Magento\Rma\Block\Adminhtml\Rma\Edit::class);

            if ($rmablockObj->getRma()) {

                $retrunVal = $rmablockObj->getRma()->getIncrementId();

            }

        }
        return $retrunVal;
    }

    /**
     * @return string
     */
    public function getHomepageUrl()
    {
        return $this->custometransHelper->getFailureUrl()
.$this->accountHelper->getRedirectConfig('aem_no_home_page');
    }
}
