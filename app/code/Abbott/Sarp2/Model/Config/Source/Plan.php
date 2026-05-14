<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Abbott\Sarp2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Aheadworks\Sarp2\Model\PlanFactory;

/**
 * Action class for My Account:Action to be set
 */
class Plan implements ArrayInterface
{
	public $PlanFactory;
    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;


	public function __construct( PlanFactory $planFactory){
		$this->PlanFactory = $planFactory;
	}
    /**
     * @return array
     */
    public function toOptionArray()
    {
		$plans = $this->PlanFactory->create()->getCollection()
			->addFieldToSelect(['plan_id','name'])
			->addFieldToFilter('status', ['eq' => 1])
			->getData();
			
			if(count($plans) > 0 && !empty($plans)){
				foreach($plans as $plan){
					$options[] = [ 'label' => $plan['name'], 'value' => $plan['plan_id']];
				}
			}
	    return $options;
    }
	
}
