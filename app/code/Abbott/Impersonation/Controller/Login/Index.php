<?php

namespace Abbott\Impersonation\Controller\Login;

use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\Impersonation\Helper\Data as Imphelper;

/**
 * LoginAsCustomer login action
 */
class Index extends \Magento\Framework\App\Action\Action
{
    public $tokenModelFactory;
    public $accountHelper;
    public $createEmptyCartForCustomer;
    public $cartManagement;
    public $quoteIdMaskFactory;
    public $quoteIdMaskResourceModel;
    public $quoteIdToMaskedQuoteId;
    public $storeManager;
    public $orderFactory;
    public $sgpRestriction;
    public $log;
    /**
     * @var imphelper
     */
    protected $imphelper;
    /**
     * @var \Abbott\Impersonation\Model\Impersonation
     */
    protected $impersonationModel;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;
    protected $_scopeConfig;


    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Abbott\Impersonation\Model\Impersonation $impersonationModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Abbott\Impersonation\Model\Impersonation $impersonationModel,
        TokenModelFactory $tokenModelFactory,
        \Abbott\MyAccount\Helper\Data $accountHelper,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        CartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Abbott\RestrictCheckout\Model\Restriction $sgpRestriction,
        Imphelper $imphelper,
		\Abbott\AwsLambda\Logger\Log $log
    ) {
        parent::__construct($context);
        $this->impersonationModel = $impersonationModel;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->accountHelper = $accountHelper;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->sgpRestriction = $sgpRestriction;
        $this->_scopeConfig = $scopeConfig;
        $this->imphelper = $imphelper;
		$this->log = $log;
    }
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {   
            $impersonation = $this->_initLogin();
            if (!$impersonation) { 
                $this->_redirect('/');
                return;
            }
            $customer = $impersonation->getCustomer();

            //Impersonation API for Similac Store START
            $imprequesturl = $this->_scopeConfig->getValue(
                'my_account/impersonation/impersonation_request_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $enable = $this->_scopeConfig->getValue(
                'my_account/impersonation/impersonation_enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($enable && $imprequesturl) {
				  $this->impersonationModel->clearCookie();
                // Call Lambda API to get cookies
                if (!$this->getImpersonationApi($imprequesturl,$customer->getId())) {
                    $this->_view->loadLayout();
                    $this->messageManager->addErrorMessage(__('Cannot login to account. Impersonation Failed.'));
                    $this->_view->renderLayout();
                    return false;
                }
				$impersonation->authenticateCustomer();
				$this->log->writeLog('After Api completion move to proceed');
				$this->messageManager->addSuccessMessage(__('You are logged in as customer: %1', $impersonation->getCustomer()->getName())); 
				$this->_redirect('*/*/proceed');				
            } else{
            //Impersonation API for Similac Store END
            try {
			    /* Log in */
                $impersonation->authenticateCustomer();
                $customer = $impersonation->getCustomer();
                $customerToken = $this->tokenModelFactory->create()->createCustomerToken($customer->getId())->getToken();
                $customerEmail = base64_encode($customer->getEmail());
                $cookieDomain = $this->accountHelper->getCookieRedirect();
                $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();
                $publicCookieMetadata->setPath('/');
                $publicCookieMetadata->setDomain($cookieDomain);
                $publicCookieMetadata->setHttpOnly(false);
                $publicCookieMetadata->setSecure(true);
                $publicCookieMetadata->setSameSite('Lax');
                $storeId = $this->storeManager->getStore()->getId();
                $this->setCookie('abt_usr', '{"email":"'.$customerEmail.'","token":"' . $customerToken . '","fname":"' . $customer->getFirstname() . '","cgroup":"' . base64_encode($customer->getGroupId()) . '"}', $publicCookieMetadata);
                $this->sgpRestriction->setRestrictionDetails();
                $cartId = $this->getCartId($customer);
                $this->setCookie('abt_cartKey', $cartId, $publicCookieMetadata);
                if ($storeId == 2) {
                    $this->setCookie('abt_te', $this->getOrdersCount($customer), $publicCookieMetadata);
                }
                $this->messageManager->addSuccessMessage(
                    __('You are logged in as customer: %1', $impersonation->getCustomer()->getName())
                ); 
                $this->_redirect('*/*/proceed');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } 
			}        
    }

    /**
     * Init login info
     * @return false || \Abbott\Impersonation\Model\Impersonation
     */
    protected function _initLogin()
    {
        $secret = $this->getRequest()->getParam('secret');
        if (!$secret || !is_string($secret)) {
            $this->messageManager->addErrorMessage(__('Cannot login to account. No secret key provided.'));
            return false;
        }

        $impersonation = $this->impersonationModel->loadNotUsed($secret);
        if ($impersonation->getLoginId()) {
            return $impersonation;
        } else {
            $this->messageManager->addErrorMessage(__('Cannot login to account. Secret key is not valid.'));
            return false;
        }
    }

    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    public function setCookie($key, $value, $metaData)
    {
        $this->getCookieManager()->setPublicCookie(
            $key,
            $value,
            $metaData
        );
    }

    private function getCartId($customer)
    {
        $customerId = $customer->getId();
        $storeId = $this->storeManager->getStore()->getId();
        try {
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($customerId, null);
            $cart =  $this->cartManagement->getCartForCustomer($customerId);
        }
        if ($storeId == 2 || $storeId == 3) {
            $cart->removeAllItems()->save();
        }
        $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        if (empty($maskedId)) {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId((int) $cart->getId());
            $this->quoteIdMaskResourceModel->save($quoteIdMask);
            $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        }
        return $maskedId;
    }

    private function getOrdersCount($customer) {
      $orders = [];
      if($customer->getId()){
        $orders = $this->orderFactory->create()->addFieldToFilter('customer_id', $customer->getId());
      }
      return count($orders);
    }

    private function getImpersonationApi($imprequesturl,$customerId)
    {
        $response = $this->imphelper->getCurlResponse($imprequesturl, $customerId);
        if (empty($response)) {
            return false;
        }
        if(is_array($response)){
           $cookieDomain = $this->accountHelper->getCookieRedirect();	
		   $path = '/';	
           $httpOnly = false;	
           $secure = true;	 
		   $expire = 'Session';
		   
		    foreach ($response as $key => $value) {		
				$publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();			
				if(is_array($value) && !empty($value)){
					$secure = (!empty($value['secure'])) ? $value['secure']: $secure;				
					if($key == "PHPSESSID" && !empty($value['path'])){						
						$path = $value['path'];
						$cookieDomain = $value['domain'];
						$httpOnly = (!empty($value['HttpOnly'])) ? $value['HttpOnly'] : $httpOnly;
											
					}					
					if(!empty($value['value'])){
						$publicCookieMetadata->setPath($path);
						$publicCookieMetadata->setDomain($cookieDomain);
						$publicCookieMetadata->setHttpOnly($httpOnly);
						$publicCookieMetadata->setSecure($secure);	
						if($key == "abt_usr"){
							$this->setCookie($key, urldecode($value['value']), $publicCookieMetadata);
						} else {
							$this->setCookie($key, $value['value'], $publicCookieMetadata);
						}
					}		
				}
			}
            $this->setCookie('mage_usr_imp', true, $publicCookieMetadata);            
		}
        return true;
    }
}