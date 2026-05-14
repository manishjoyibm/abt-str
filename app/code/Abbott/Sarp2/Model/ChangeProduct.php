<?php

declare(strict_types = 1);

namespace Abbott\Sarp2\Model;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use \Magento\Catalog\Model\ProductRepository;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Aheadworks\Sarp2\Model\Profile\ItemFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Abbott\Sarp2\Helper\Data;
use Abbott\Sarp2\Helper\ChangeSubscription;
use Magento\Framework\DataObject\Copy;
use Abbott\Subscriptionhistory\Helper\Data as HistoryDataLog;

/**
 * Update customer data resolver
 */
class ChangeProduct {


	public $profileRepository;
 public $productRepository;
 public $profileDataInterface;
 public $planRepository;
 /**
  * @var \Magento\Framework\App\ResourceConnection
  */
 public $resource;
 public $priceCalculation;
 public $helper;
 public $profileItem;
 public $itemInterface;
 public $updateSubscribe;
 public $historyDataLog;
 const SUBSCIPTION_PLAN_CHANGE_PRODUCT_EVENT = "subscription_profile_change_product";
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     *
     * @var \Aheadworks\Sarp2\Model\Profile\ItemFactory 
     */
    protected $itemFactory;
      
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionsRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;
    
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface 
     */
    private $_storeManager;

    /**
     * @var Copy
     */
    private $objectCopyService;
    
   
    /**
     *
     * @var type 
     */
    protected $profile;
   

   /**
    * 
    * @param ProfileRepositoryInterface $profileRepository
    * @param ProductRepository $productRepository
    * @param SubscriptionOptionRepositoryInterface $optionsRepository
    * @param PlanRepositoryInterface $planRepository
    * @param ResourceConnection $resource
    * @param \Abbott\PriceInvGql\Model\Product\Subscription\PriceCalculation $priceCalculation
    * @param ItemFactory $itemFactory
    * @param \Aheadworks\Sarp2\Api\Data\ProfileItemInterface $profileItem
    * @param \Aheadworks\Sarp2\Api\ProfileItemRepositoryInterface $itemInterface
    * @param DataObjectProcessor $dataObjectProcessor
    * @param Data $helper
    * @param Copy $objectCopyService
    * @param ChangeSubscription $updateSubscribe
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @return type
    */
    public function __construct(
            ProfileRepositoryInterface $profileRepository, 
            ProductRepository $productRepository, 
            SubscriptionOptionRepositoryInterface $optionsRepository, 
            PlanRepositoryInterface $planRepository, 
            ResourceConnection $resource, 
            \Abbott\PriceInvGql\Model\Product\Subscription\PriceCalculation $priceCalculation, 
            \Aheadworks\Sarp2\Model\Profile\ItemFactory $itemFactory, 
            \Aheadworks\Sarp2\Api\Data\ProfileItemInterface $profileItem, 
            \Aheadworks\Sarp2\Api\ProfileItemRepositoryInterface $itemInterface,
            DataObjectProcessor $dataObjectProcessor, 
            Data $helper, 
            Copy $objectCopyService,
            ChangeSubscription $updateSubscribe, 
            \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Aheadworks\Sarp2\Api\Data\ProfileInterface $profileDataInterface,
        HistoryDataLog $historyDataLog
    ) {        
        $this->profileRepository = $profileRepository;
        $this->productRepository = $productRepository;
	$this->profileDataInterface = $profileDataInterface;
        $this->optionsRepository = $optionsRepository;
        $this->planRepository = $planRepository;
        $this->resource = $resource;
        $this->priceCalculation = $priceCalculation;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->helper = $helper;
        $this->profileItem = $profileItem;
        $this->itemInterface = $itemInterface;
        $this->updateSubscribe = $updateSubscribe;
        $this->_storeManager = $storeManager;
		$this->itemFactory = $itemFactory;
        $this->historyDataLog = $historyDataLog;
    }

    /**
     * Executes the change product in subscription
     */
    public function execute(array $input): array
    {
        try{
                $profile = $this->profileRepository->get($input['profile_id']);
                $newProduct =  $this->productRepository->get($input['sku']);
		$profilePlanId = $profile->getPlanId();
		$canChangeProduct = $this->helper->getChangeProductUrl($profile->getStoreId(), $profilePlanId, $profile->getProfileId(), $input['old_sku']);
		if(!empty($canChangeProduct)) {
                    $profileType = $this->getProfileType($profile->getPlanId());
                    //validate old sku against the profile
                    $isOldSkuValid = $this->validateOldSku($profile, $input['old_sku']);
                    if(!empty($isOldSkuValid)){
                        //validate new sku for same subscription type
                        $isValid = $this->validateNewSku($profile, $newProduct, $profileType);
                        if(is_numeric($isValid)){	
                            $productName = $newProduct->getName();
                            $productId = $newProduct->getId();			
                            $qty = $input['qty'];
                            $isValidProductQty = $this->validateProductQty($newProduct, $qty);
                            if(!empty($isValidProductQty) && $isValidProductQty == 1){
                                    return $data = ['status' => true, 'message' => "Invalid Product Quantity"];     
                            }
                           
                            //Get current moth for subscription
                            $profileDefinition = $profile->getProfileDefinition();
				$autoTrialPrice = 0;
				$autoRegularPrice = 0;
                            if($profileType == 0){
                                $autoTrialPrice = $this->priceCalculation->getAutoTrialPrice($productId, $profilePlanId);
                                $autoRegularPrice = $this->priceCalculation->getAutoRegularPrice($productId, $profilePlanId);
                            }
                            //create product options
                            $newPlan = $this->planRepository->get($profilePlanId);
                            $newOption = $this->getOptionByPlan($productId, $profilePlanId);
                            $oldProfileData = [ self::SUBSCIPTION_PLAN_CHANGE_PRODUCT_EVENT => [$input['old_sku'] => $isOldSkuValid->getName(), 'qty' => $isOldSkuValid->getQty()]];
							if(!empty($newOption)){
								 //Remove Old Product from profile
								$this->unsetOldSkuFromProfile($input['profile_id'], $input['old_sku']);

								$newPlanArray = $this->dataObjectProcessor->buildOutputDataArray(
										$newPlan, PlanInterface::class
								);
								$newOptionArray = $this->dataObjectProcessor->buildOutputDataArray(
										$newOption, SubscriptionOptionInterface::class
								);
                            $productOptions['info_buyRequest']['aw_sarp2_subscription_type'] = $newOption->getOptionId();
                            $productOptions['aw_sarp2_subscription_plan'] = $newPlanArray;
                            $productOptions['aw_sarp2_subscription_option'] = $newOptionArray;

                            $item = $this->profileItem;
                            $item->setProfileId($input['profile_id']);
                            $item->setProductId($productId);
                            $item->setProductType($newProduct->getTypeId());
                            $item->setStoreId($profile->getStoreId());
                            $item->setSku($input['sku']);
                            $item->setName($newProduct->getName());
                            $item->setWeight($newProduct->getWeight());
                            $item->setQty($input['qty']);
                            $item->setTrialPrice($autoTrialPrice);
                            $item->setBasePrice($newProduct->getPrice());
                            $item->setBaseTrialPrice($autoTrialPrice);
                            $item->setRegularPrice($autoRegularPrice);
                            $item->setBaseRegularPrice($autoRegularPrice);
                            $item->setRegularPriceInclTax($autoRegularPrice);
                            $item->setBaseRegularPriceInclTax($autoRegularPrice);
                            $item->setRegularRowTotal($autoRegularPrice * $input['qty']);
                            $item->setBaseRegularRowTotal($autoRegularPrice * $input['qty']);
                            $item->setRegularRowTotalInclTax($autoRegularPrice * $input['qty']);
                            $item->setBaseRegularRowTotalInclTax($autoRegularPrice * $input['qty']);
                            $item->setOriginalPrice($newProduct->getPrice());
                            $item->setBaseOriginalPrice($newProduct->getFinalPrice());
                            $item->setProductOptions($productOptions);
                            $this->itemInterface->save($item);
                             $newProfileData = [ self::SUBSCIPTION_PLAN_CHANGE_PRODUCT_EVENT => [$input['sku'] => $newProduct->getName(), 'qty' => $input['qty']]];
                            //set total for subscription profile
                            $this->collectProfileData($profile->getProfileId());
                            if($this->historyDataLog->getSubscriptionHistoryStatus($profile->getStoreId()) && $input['sku']!= $input['old_sku']){
                                $this->historyDataLog->prepareFrontendData($profile, self::SUBSCIPTION_PLAN_CHANGE_PRODUCT_EVENT, $oldProfileData, $newProfileData);
                            }
                            //send subscription update email 
                            if ($this->helper->getUpdateMailEnabled()) {
                                $this->updateSubscribe->updateSubscriptionNotification($profile->getProfileId());
                            }
                            return $data = ['status' => true, 'message' => "Subscription Updated Successfully"];       
                        } else {
							return $data =['status' => false, 'message' => "Product is not supported for Subscription Options"]; 
						}
						} else {
                            return $data =['status' => false, 'message' => $isValid];  
                        }
						
                    } else {
                        return $data =['status' => false, 'message' => "Please verify the old product from Subscription"];  
                    }
                } else {
			return $data =['status' => false, 'message' => "Profile is not supported for Change Product"]; 
		}
	} catch(\Exception $e){
	    return $data = ['status' => false, 'message' => $e->getMessage()];  
	}
    }
	
    /**
     * Get Profile Type
     * @param type $planId
     * @return boolean
     */
    public function getProfileType($planId){
            if($planId){
                    $newPlan = $this->planRepository->get($planId);
                    if(!empty($newPlan)){
                            return $newPlan->getIsProgressive();
                    }
            }
            return false;

    }
	
	
    /**
     * Get product option
     * @param type $productId
     * @return boolean
     */
    private function getOptionByProductId($productId, $planId) {
        $planType = [];
		if ($productId) {			
			$subscriptionOptions = $this->optionsRepository->getList($productId);
			if(!empty($subscriptionOptions)){
            foreach ($subscriptionOptions as $option) { //print_r($option);
                //get product option website id mapped
                if ($option->getPlanId() == $planId && ($option->getWebsiteId() == $this->getStoreId() || $option->getWebsiteId() == 0)) {
                    $planType[$option->getPlanId()] = ($this->getProfileType($option->getPlanId())) ? $this->getProfileType($option->getPlanId()) : 0;
                }
            }
            return $planType;
			} else {
				return $planType;
			}
        }
        return $planType;
    }
    
    /**
     * Validate new sku
     * @param type $profile
     * @param type $newProduct
     * @param type $profileType
     * @return string
     */
    private function validateNewSku($profile, $newProduct, $profileType){
            $newProductSubscriptioStatus = $newProduct->getIsSubscription();
			$profileItems = $profile->getItems();
			$profileSku = [];
		
			if(!empty($profileItems)){
				foreach($profileItems as $item):
				array_push($profileSku, $item->getSku());
				endforeach;
			}
		
			if(!empty($profileSku) && !empty(in_array($newProduct->getSku(), $profileSku))){
				return $message = "The new selected product already match your current subscription";
			}
            if($newProductSubscriptioStatus){

                    //check for relative profile type for new sku
             $productSubscriptionType = $this->getOptionByProductId($newProduct->getId(), $profile->getPlanId());
        if (count($productSubscriptionType) > 0 && !empty($productSubscriptionType)) {
            $planId = array_search($profileType, $productSubscriptionType);
            if (!empty($planId)) {
                $message = $planId;
                     } else{
                             $message = "The new selected product does not match your current subscription plan";
                     }
            } else{
                    $message = "Subscription options are not set for the product";
            }

            } else{
                    $message = "Product is not enabled for subscription";
            }
            return $message;
    }
	
    /**
     * Validate old sku
     * @param type $profile
     * @param type $oldSku
     * @return boolean
     */
    private function validateOldSku($profile, $oldSku){
            if(!empty($profile) && !empty($oldSku)){
                    $items = $profile->getItems();
                    foreach($items as $item):
                            if($oldSku == $item->getSku()){
                                    return $item;
                            }			
                    endforeach;
            }
            return false;
    }
	
    /**
     * Remove old sku from profile
     * @param type $profileId
     * @param type $oldSku
     * @return boolean
     */
    public function unsetOldSkuFromProfile($profileId, $oldSku, $addHistoryLog = ""){
	if($profileId && $oldSku){
		$collection = $this->itemFactory->create()->getCollection()
			->addFieldToFilter('profile_id', ['eq' => $profileId])
			->addFieldToFilter('sku', ['eq' => $oldSku]);

			if($collection->getSize() == 1){
                $collectionData = $collection->getData();
                $pName = $collectionData[0]['name'];
                $pQty = $collectionData[0]['qty'];
				$collection->walk('delete');
			}	
            if($addHistoryLog !=""  && $this->historyDataLog->getSubscriptionHistoryStatus($addHistoryLog)){
				$profile = $this->profileRepository->get($profileId);
				$oldData = [\Abbott\Sarp2\Controller\Profile\Remove::SUBSCIPTION_PLAN_REMOVE_PRODUCT_EVENT => [$oldSku => $pName, 'qty' => $pQty]];
				$newData = [\Abbott\Sarp2\Controller\Profile\Remove::SUBSCIPTION_PLAN_REMOVE_PRODUCT_EVENT => ''];
				$this->historyDataLog->prepareFrontendData($profile, \Abbott\Sarp2\Controller\Profile\Remove::SUBSCIPTION_PLAN_REMOVE_PRODUCT_EVENT, $oldData, $newData);
			}            
	}
	return false;
    }
	
	
    /**
     * Set profile total
     * @param type $profile
     */
    public function collectProfileData($profileId){		
            $oldProfile = $this->profileRepository->get($profileId);
	   $items = $oldProfile->getItems();
            $qty = 0;
            $initialSubTotal = 0;
            $baseinitialSubTotal = 0;
            $intialRowTotalInclTax = 0;
            $baseIntialRowTotalInclTax = 0;
            $trialSubTotal  = 0;
            $trialBaseSubTotal  = 0;
            $trialSubTotalIncTax = 0;
            $basetrialSubTotalIncTax = 0;
            $trialGrandTotal = 0;
            $basetrialGrandTotal = 0;
            $trialPrice = 0;
            $baseTrialPrice =0;
            $trialPriceIncTax = 0;
            $baseTrialPriceIncTax = 0;
            $trialRowTotal = 0;
            $baseTrialRowTotal = 0;
            $regularPrice = 0;
            $baseregularPrice = 0;
            $baseregularPriceIncTax = 0;
            $regularRowTotal = 0;
            $baseregularRowTotal = 0;
            $baseregularToatlIncTax = 0;
            foreach($items as $item):
                $qty = $qty + $item->getQty();
		 $initialSubTotal = $initialSubTotal + $item->getInitialRowTotal();
                $baseinitialSubTotal = $baseinitialSubTotal + $item->getBaseInitialRowTotal();
                $intialRowTotalInclTax = $intialRowTotalInclTax + $item->getIntialRowTotalInclTax();
                $baseIntialRowTotalInclTax = $baseIntialRowTotalInclTax + $item->getBaseIntialRowTotalInclTax();
                $trialSubTotal = $trialSubTotal + $item->getTrialSubTotal();
                $trialBaseSubTotal = $trialBaseSubTotal + $item->getBaseTrialRowTotal();
                $trialSubTotalIncTax = $trialSubTotalIncTax + $item->getTrialRowTotal();
                $basetrialSubTotalIncTax = $basetrialSubTotalIncTax + $item->getBaseTrialSubTotalInclTax();
                $trialGrandTotal = $trialGrandTotal + $item->getTrialRowTotal();
                $basetrialGrandTotal = $basetrialGrandTotal + $item->getBaseTrialRowTotal();
                $trialPrice = $trialPrice + $item->getTrialPrice();
                $baseTrialPrice = $baseTrialPrice + $item->getBaseTrialPrice();
                $trialPriceIncTax = $trialPriceIncTax + $item->getTrialPriceInclTax();
                $baseTrialPriceIncTax = $baseTrialPriceIncTax + $item->getBaseTrialPriceInclTax();
                $trialRowTotal = $trialRowTotal + $item->getTrialRowTotal();
                $baseTrialRowTotal = $baseTrialRowTotal + $item->getBaseTrialRowTotal();
                $regularPrice = $regularPrice + $item->getRegularPrice();
                $baseregularPrice = $baseregularPrice + $item->getBaseRegularPrice();
                $baseregularPriceIncTax = $baseregularPriceIncTax + $item->getBaseRegularPriceInclTax();
                $regularRowTotal = $regularRowTotal + $item->getRegularRowTotal();
                $baseregularRowTotal = $baseregularRowTotal + $item->getBaseRegularRowTotal();
                $baseregularToatlIncTax = $baseregularToatlIncTax + $item->getBaseRegularToatlInclTax();
            endforeach;
		$profile = $this->profileDataInterface;
		$profile->setProfileId($profileId);
            $profile->setItemsQty($qty);		
            $profile->setInitialSubtotal($initialSubTotal); //initial_subtotal
            $profile->setBaseInitialSubtotal($baseinitialSubTotal); //base_initial_subtotal
            $profile->setInitialGrandTotal($baseinitialSubTotal);//initial_grand_total
            $profile->setBaseInitialGrandTotal($baseinitialSubTotal);//base_initial_grand_total
            $profile->setTrialSubtotal($trialSubTotal);//trial_subtotal
            $profile->setBaseTrialSubtotal($trialBaseSubTotal);//base_trial_subtotal
            $profile->setInitialSubtotalInclTax($intialRowTotalInclTax);//initial_subtotal_incl_tax
            $profile->setBaseInitialSubtotalInclTax($baseIntialRowTotalInclTax);//base_initial_subtotal_incl_tax
            $profile->setTrialSubtotalInclTax($trialSubTotalIncTax); //trial_subtotal_incl_tax
            $profile->setBaseTrialSubTotalInclTax($basetrialSubTotalIncTax);//base_trial_subtotal_incl_tax
            $profile->setTrialGrandTotal($trialGrandTotal);//trial_grand_total
            $profile->setBaseTrialGrandTotal($basetrialGrandTotal);//base_trial_grand_total
			$profile->setStoreId($oldProfile->getStoreId());
			$profile->setPlanDefinitionId($oldProfile->getPlanDefinitionId());
			$profile->setStartDate($oldProfile->getStartDate());
			$profile->setPaymentTokenId($oldProfile->getPaymentTokenId());
            $profile->setTrialGrandTotal($trialSubTotal);
            $profile->setBaseTrialGrandTotal($trialBaseSubTotal);
            $profile->setRegularSubtotal($regularPrice);//regular_subtotal
            $profile->setBaseRegularSubtotal($baseregularPrice);//base_regular_subtotal
            $profile->setRegularGrandTotal($regularRowTotal);
            $profile->setBaseRegularGrandTotal($baseregularRowTotal);
            $profile->setRegularSubtotalInclTax($baseregularToatlIncTax);
            $profile->setBaseRegularSubtotalInclTax($baseregularToatlIncTax);
            $profile->save();
    }

    /**
     * Retrieve get option by plan
     * @param type $productId
     * @param type $planId
     * @return type
     */    
    private function getOptionByPlan($productId, $planId)
    {
        $subscriptionOptions = $this->optionsRepository->getList($productId);
        /** @var SubscriptionOptionInterface $option */
        foreach ($subscriptionOptions as $option) {
            if ($planId == $option->getPlanId()) { 
                return $option;
            }
        }
        return null;
    }
	
    /**
     * Validate Product Sku
     * @param type $product
     * @param type $qty
     * @return boolean
     */
    private function validateProductQty($product, $qty){
        if(!empty($product) && !empty($qty)){
                $minQty = $product->getData('cans_y_min_update');
                $maxQty = $product->getData('cans_x_max_update');
                if($qty >= $minQty && $qty <= $maxQty){
                        return false;
                } else {
                        return true;
                }
        }
    }

    /**
     * Get store identifier
     * @return  int
     */
    public function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }

}
