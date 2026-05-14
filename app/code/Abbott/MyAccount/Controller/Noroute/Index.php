<?php

namespace Abbott\MyAccount\Controller\Noroute;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends \Magento\Framework\App\Action\Action
{

    public $resultRedirect;
    public $helper;
    /**
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * Construct function
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Abbott\CustomerTransistion\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $result,
        \Abbott\CustomerTransistion\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->resultRedirect = $result;
        $this->helper = $helper;
        $this->urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * Render CMS 404 Not found page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $currentUrl = $this->urlInterface->getCurrentUrl();
        $basename = basename($currentUrl);
        $resultRedirect = $this->resultRedirect->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->helper->getFailureUrl().$basename);
        return $resultRedirect;
    }
}
