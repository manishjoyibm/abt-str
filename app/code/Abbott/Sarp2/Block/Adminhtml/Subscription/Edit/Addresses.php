<?php

namespace Abbott\Sarp2\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\Address\Resolver\FullName as FullNameResolver;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\Customer;
use Magento\Directory\Model\CountryFactory;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface as AwSaprd2TokenIterface;

class Addresses extends Template
{
    public $jsonHelper;
    public $awSarp2Token;
    /**
     * @var FullNameResolver
     */
    private $fullNameResolver;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var ProfileInterface
     */
    private $profile;

    public $paymenttokenmanagement;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Abbott_Sarp2::subscription/edit/addresses.phtml';

    public $customer;
    /**
     * @param Context $context
     * @param FullNameResolver $fullNameResolver
     * @param CountryFactory $countryFactory
     * @param PaymentMethodListInterface $paymentMethodList
     * @param array $data
     */
    public function __construct(
        Context $context,
        FullNameResolver $fullNameResolver,
        CountryFactory $countryFactory,
        PaymentMethodListInterface $paymentMethodList,
        Customer $customer,
        PaymentTokenManagementInterface $paymenttokenmanagement,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        AwSaprd2TokenIterface $awSarp2Token,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->fullNameResolver = $fullNameResolver;
        $this->countryFactory = $countryFactory;
        $this->paymentMethodList = $paymentMethodList;
        $this->customer = $customer;
        $this->paymenttokenmanagement = $paymenttokenmanagement;
        $this->jsonHelper = $jsonHelper;
        $this->awSarp2Token = $awSarp2Token;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * todo: M2SARP-382
     * Get full name
     *
     * @param ProfileAddressInterface $address
     * @return string
     */
    public function getFullName($address)
    {
        return $this->fullNameResolver->getFullName($address);
    }

    /**
     * Get country name
     *
     * @param string $countryId
     * @return string
     */
    public function getCountryName($countryId)
    {
        $country = $this->countryFactory->create()->loadByCode($countryId);
        return $country->getName();
    }

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getPaymentMethodTitle()
    {
        $profile = $this->getProfile();
        $methods = $this->paymentMethodList->getList($profile->getStoreId());
        foreach ($methods as $method) {
            if ($method->getCode() == $profile->getPaymentMethod()) {
                return $method->getTitle();
            }
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getCustomerAddress($customerId = null)
    {
        if ($customerId) {
            $customerData = $this->customer->load($customerId);
            $addresses = $customerData->getAddresses();
            return $customerData->getAddressCollection()->addFieldToFilter('parent_id', $customerId)->getData();
        }
        return null;
    }

    public function getCustomerVaults($customerId = null)
    {
        if ($customerId) {
            $cardList = $this->paymenttokenmanagement->getListByCustomerId($customerId);
            return $cardList;
        }
        return null;
    }

    public function getLast4Digits($data = null)
    {
        if ($data) {
            $data = $this->jsonHelper->jsonDecode($data);
            if (isset($data['maskedCC'])) {
                return $data['maskedCC'];
            }
        }
        return null;
    }

    public function getAwSarp2TokenDetails($token_id = null)
    {
        if ($token_id) {
            $tokenData = $this->awSarp2Token->get($token_id);
            return $tokenData;
        }
        return null;

    }
}
