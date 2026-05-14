<?php
namespace Abbott\RestrictCheckout\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;

class ManageAttribute extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    public function __construct(Context $context, array $data = [])
    {
        $this->authorization = $context->getAuthorization();
        parent::__construct($context, $data);
    }

    /**
     * Check permissions for Purchase Limit Attribute
     * @return boolean
     */
    public function getIsAllowed()
    {
        return $this->authorization->isAllowed('Abbott_RestrictCheckout::allow_purchase_limit');
    }

}
