<?php
namespace Abbott\MyAccount\Plugin\Model\Customer;

use Abbott\MyAccount\Helper\Data;
use Abbott\MyAccount\Helper\LinkData;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AttributeMetadataResolver
{

  /**
     * @var bool
     */
    public $similacnewflag;
    /**
     * @var \Abbott\MyAccount\Helper\Data
     */
    public $_helper;
    public $_linkdata;
    public $request;
    public $_customerRepositoryInterface;
    /**
     * Construct function
     *
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Data $helper
     * @param LinkData $linkdata
     */
    public function __construct(
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Data $helper,
        LinkData $linkdata
    ) {
        $this->similacnewflag = false;
        $this->_helper = $helper;
        $this->_linkdata = $linkdata;
        $this->request = $request;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * AfterGetAttributesMeta function
     *
     * @param \Magento\Customer\Model\AttributeMetadataResolver $attribute
     * @param $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetAttributesMeta(\Magento\Customer\Model\AttributeMetadataResolver $attribute, $result)
    {
        if ($this->request->getParam('id') != null) {
            $customerId = $this->request->getParam('id');
            $customer = $this->_customerRepositoryInterface->getById($customerId);
            $websiteId = $customer->getWebsiteId();
            foreach ((array)$result['arguments']['data']['config']['label'] as $labelobj) {
                if (($labelobj == 'Email') && $this->_linkdata->getEmailEditDisableFlag($websiteId)) {
                    $result['arguments']['data']['config']['disabled'] = true;
                }
            }
        }
        return$result;
    }
}
