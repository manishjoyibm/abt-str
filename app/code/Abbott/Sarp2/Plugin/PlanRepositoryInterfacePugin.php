<?php

namespace Abbott\Sarp2\Plugin;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;

use Aheadworks\Sarp2\Api\Data\PlanExtensionFactory;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\PlanFactory;
use Aheadworks\Sarp2\Api\Data\PlanSearchResultsInterface;

/**
 * Class PlanRepositoryInterfacePlugin
 */
class PlanRepositoryInterfacePugin {

    public $planFactory;
    public $_request;
    /**
     * @var CartExtensionFactory
     */
    private $extensionFactory;

    /**
     * 
     * @param PlanRepositoryInterface $planRepository
     * @param PlanFactory $planFactory
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
     PlanRepositoryInterface $planRepository, PlanFactory $planFactory, \Magento\Framework\App\RequestInterface $request
    ) {
        $this->planFactory = $planFactory;
        $this->_request = $request;
    }

    /**
     * 
     * @param PlanRepositoryInterface $subject
     * @param PlanInterface $result
     * @return PlanInterface
     */
    public function afterSave(
    PlanRepositoryInterface $subject, PlanInterface $result
    ) {
        $postData = $this->_request->getParams();
        $newPlan = $this->planFactory->create()->load($result->getPlanId());
        $newPlan
                ->setPlanId($result->getPlanId())
                ->setPlanDefinitionId($result->getDefinitionId())
                ->setIsProgressive($postData['is_progressive'])
                ->setIsCancelOrder($postData['is_cancel_order']);
        $newPlan->save();
        return $result;
    }

}
