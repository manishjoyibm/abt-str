<?php

namespace Abbott\Sarp2\Plugin;


/**
 * Class CustomerSave
 * @package Abbott\Sarp2\Plugin
 */
class CustomerSave
{
   /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;
	
	/**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
	private $redirectFactory;

    /**
     * CustomerSave constructor.
     * @param \Magento\Customer\Controller\Address\FormPost $customerSave
     */
    public function __construct(
	   \Magento\Framework\Controller\ResultFactory $resultFactory,
       \Magento\Framework\App\Response\RedirectInterface $redirect,
	   \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    )
    {
       $this->resultFactory = $resultFactory;
       $this->redirect = $redirect;
	   $this->redirectFactory = $redirectFactory;
    }

    /**
     * @param \Magento\Customer\Controller\Address\FormPost $customerSave
     * @return boolean
     */
    public function afterExecute(\Magento\Customer\Controller\Address\FormPost $customerSave)
    {
		$resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		if(!empty($customerSave->getRequest()->getPostValue('subscription_profile_address_save'))) :
            // if you want to redirect to the previous page
            return $resultRedirect->setUrl($this->redirect->getRefererUrl());
		else:
			return $this->redirectFactory->create()->setPath('customer/address/index');
		endif;
		
    }
}
