<?php

declare(strict_types=1);

/**
 * Update product attribute values
 */
namespace Abbott\AdditionalAttributes\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;

class UpdatePlansSaveBefore implements ObserverInterface
{

    public $request;
    public $productAction;
    public $repository;
    /**
     * update plan constructor.
     * @param ObjectManagerInterface $objectManager
     * @param RequestInterface $request
     * @param Registry $registry
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Store\Api\StoreRepositoryInterface $repository
    ) {
        $this->request = $request;
        $this->productAction = $productAction;
        $this->repository = $repository;
    }

    /**
     * @param Observer $observer
     * @return Observer
     */
    public function execute(Observer $observer)
    {

        $product = $observer->getEvent()->getProduct();
        $reqeustParams = $this->request->getParams();

        $plans = !empty($reqeustParams['product']['aw_sarp2_subscription_options'])
         ? $reqeustParams['product']['aw_sarp2_subscription_options']
         : [];
        $store = $reqeustParams['store'];
        $storesdata = $this->repository->getList();
        foreach ($storesdata as $stored) {
                $storesarr[] = $stored->getId();
        }
        $this->updatePlans($plans, $store, $storesarr, $product);

    }
    public function updatePlans($plans, $store, $allstores, $product)
    {
        $allstoreplan = array();
        $plansattr = $product->getResource()->getAttribute('plans');
        $zeroplanOptionid = $plansattr->getSource()->getOptionId('0');
        if (!empty($plans)) {
        $planarr = $this->getPlanArray($plans, $plansattr);
        $withother = false;
        foreach ($planarr as $websiteid => $planid) {
            $allwebid[] = $websiteid;
            if ($websiteid != 0) {
                $withother = true;
                $planid = array_merge($allstoreplan, $planid);
            }
            $this->productAction->updateAttributes(
                array($product->getId()),
                ['plans' => implode(',', $planid)],
                (int)$websiteid
            );
        }

            if (!$withother) {
                $allwebid[] =  (int) $store;
                $this->productAction->updateAttributes(
                    array($product->getId()),
                    ['plans' => implode(',', $planid)],
                    (int) $store
                );
            }
            $remainingstoreid = array_diff($allstores, $allwebid);
            foreach ($remainingstoreid as $rstid) {
                $this->productAction->updateAttributes(
                    array($product->getId()),
                    ['plans' => implode(',', array($zeroplanOptionid))],
                    (int) $rstid
                );
            }
        } else {
            $this->productAction->updateAttributes(
                array($product->getId()),
                ['plans' => implode(',', array($zeroplanOptionid))],
                (int) $store
            );
        }

    }


    private function getPlanArray($plans, $plansattr)
    {
        $planarr = [];
        foreach ($plans as $plan) {
            if ($plansattr->usesSource()) {
                $optionId = $plansattr->getSource()->getOptionId($plan['plan_id']);
                if ($plan['website_id'] == 0) {
                    $allstoreplan[] = $optionId;
                }
                $planarr[$plan['website_id']][] = $optionId;
            }
        }
        return $planarr;
    }
}
