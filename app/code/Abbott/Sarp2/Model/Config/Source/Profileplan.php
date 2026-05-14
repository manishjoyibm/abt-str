<?php

/* 
 * To Get the plans to render it as select option
 */

namespace Abbott\Sarp2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Abbott\Sarp2\Model\Product\Subscription\Option\Source\Backend as BackendSubscriptionOptionSource;


/**
 * Profileplan class
 */
class Profileplan implements ArrayInterface
{

    public $request;
 public $profileRepository;
 public $subscriptionOptionSource;
 /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ProfileRepositoryInterface $profileRepository
     * @param BackendSubscriptionOptionSource $subscriptionOptionSource
     */
	public function __construct(\Magento\Framework\App\RequestInterface $request, ProfileRepositoryInterface $profileRepository, 
	BackendSubscriptionOptionSource $subscriptionOptionSource){
		$this->request = $request;
		$this->profileRepository = $profileRepository;
		$this->subscriptionOptionSource = $subscriptionOptionSource;
	}

	/**
     * Get subscription option array
     *
     * @return array
     */  
    public function toOptionArray()
    {
        $intersectOptionArray = [];
		$intersectOption = [];
		$otherOptions = [];
		$profileId = (int)$this->request->getParam('profile_id');
		$profile = $this->profileRepository->get($profileId);
        foreach ($profile->getItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $options = $this->subscriptionOptionSource->getPlanOptionArray($item->getProductId(), $profile->getStoreId());
            $intersectOptionArray = $intersectOptionArray
                ? array_intersect_key($options, $intersectOptionArray)
                : $options;
        }
		if($intersectOptionArray != null)
		{
			foreach($intersectOptionArray as $optionkey => $optionarr)
			{
			if($profile->getPlanId() == $optionkey)
					{					
						$intersectOption[] = [ 'label' => $optionarr, 'value' => $optionkey];
					}
					else
					{
						$otherOptions[] = [ 'label' => $optionarr, 'value' => $optionkey];	
					}
			}
		}		
		$options = array_merge($intersectOption, $otherOptions);		
        return $options;
    }
}