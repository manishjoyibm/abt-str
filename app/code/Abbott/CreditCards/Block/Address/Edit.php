<?php
namespace Abbott\CreditCards\Block\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Braintree\Gateway\Config\Config;
use Magento\Framework\Session\SessionManagerInterface;
use Abbott\CreditCards\Model\Adapter\BraintreeAdapterFactory;
use PayPal\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfig;

/**
 * Customer address edit block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since                                          100.0.2
 */
class Edit extends \Magento\Directory\Block\Data
{
    public $config;
    /**
     * @var \Abbott\CreditCards\Model\Adapter\BraintreeAdapterFactory
     */
    public $adapterFactory;
    public $session;
    public $paymentTokenManagement;
    public $ccConfig;
    public $assetSource;
    public $linkAddress;
    public $_actionFlag;
    /**
     * @var \Magento\Customer\Api\Data\AddressInterface|null
     */
    protected $address = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * @var array
     */
    private $icons = [];
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context                 $context
     * @param \Magento\Directory\Helper\Data                                   $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface                         $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config                         $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory  $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\Session                                  $customerSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface                 $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory               $addressDataFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer                 $currentCustomer
     * @param \Magento\Framework\Api\DataObjectHelper                          $dataObjectHelper
     * @param \Abbott\CreditCards\Model\AddressPaymentTokenLink                $linkAddress
     * @param Config                                                           $config
     * @param BraintreeAdapterFactory                                          $adapterFactory
     * @param SessionManagerInterface                                          $session
     * @param PaymentTokenManagementInterface                                  $paymentTokenManagement
     * @param CcConfig                                                         $ccConfig
     * @param Source                                                           $assetSource
     * @param array                                                            $data
     * @param AddressMetadataInterface|null                                    $addressMetadata
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Abbott\CreditCards\Model\AddressPaymentTokenLink $linkAddress,
        Config $config,
        BraintreeAdapterFactory $adapterFactory,
        SessionManagerInterface $session,
        PaymentTokenManagementInterface $paymentTokenManagement,
        CcConfig $ccConfig,
        Source $assetSource,
        array $data = [],
        AddressMetadataInterface $addressMetadata = null
    ) {
        $this->customerSession = $customerSession;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->currentCustomer = $currentCustomer;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->config = $config;
        $this->adapterFactory = $adapterFactory;
        $this->session = $session;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
        $this->linkAddress = $linkAddress;
        $this->addressMetadata = $addressMetadata ?: ObjectManager::getInstance()->get(AddressMetadataInterface::class);
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        $param = $this->getRequest()->getParam('public_hash');
        if ($param) {
            $this->pageConfig->getTitle()->set('Edit Card');
        } else {
            $this->pageConfig->getTitle()->set('Add New Card');
        }
        return $this;
    }

    /**
     * Get Card Last Digits
     *
     * @param  string $publicHash
     * @return string
     */
    public function getCardDetails($publicHash)
    {
        if ($publicHash) {
            $paymentToken = $this->paymentTokenManagement->getByPublicHash(
                $publicHash,
                $this->customerSession->getCustomerId()
            );
            $cardDetailsJSON = $paymentToken->getData('details');
            $cardDetails = json_decode($cardDetailsJSON, true);

            return $cardDetails['maskedCC'];
        }
    }

     /**
      * Returns address object
      *
      * @param  string $publicHash
      * @return \Magento\Customer\Api\Data\AddressInterface
      */
    public function initAddressObject($publicHash)
    {
        if ($publicHash) {
            $paymentToken = $this->paymentTokenManagement->getByPublicHash(
                $publicHash,
                $this->customerSession->getCustomerId()
            );
            $addId = $this->linkAddress->getAddressIdByPaymentId($paymentToken->getEntityId());
            if ($addId) {
                try {
                    $this->address = $this->addressRepository->getById($addId);
                    if ($this->address->getCustomerId() != $this->customerSession->getCustomerId()) {
                        $this->address = null;
                    }
                } catch (NoSuchEntityException $e) {
                    $this->address = null;
                }
            }
        }
        if ($this->address === null || !$this->address->getId()) {
            $this->address = $this->addressDataFactory->create();
            $customer = $this->getCustomer();
            $this->address->setPrefix($customer->getPrefix());
            $this->address->setFirstname($customer->getFirstname());
            $this->address->setMiddlename($customer->getMiddlename());
            $this->address->setLastname($customer->getLastname());
            $this->address->setSuffix($customer->getSuffix());
        }
        return $this->address;
    }

    /**
     * Generate name block html.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(\Magento\Customer\Block\Widget\Name::class)
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * Return the associated address.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param  int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the country Id.
     *
     * @return int|null|string
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        $regionId = $this->getAddress()->getRegionId();
        if ($regionId) {
            return $regionId;
        } else {
            return 0;
        }
    }

    /**
     * Retrieve the number of addresses associated with the customer given a customer Id.
     *
     * @return int
     */
    public function getCustomerAddressCount()
    {
        return count($this->getCustomer()->getAddresses());
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->currentCustomer->getCustomer();
    }

    /**
     * Get config value.
     *
     * @param  string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * GetAt function
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getAT()
    {
        if (empty($this->clientToken)) {
            $params = [];
            $storeId = $this->session->getStoreId();
            $merchantAccountId = $this->config->getMerchantAccountId($storeId);
            if (!empty($merchantAccountId)) {
                $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
            }
            $this->clientToken = $this->adapterFactory->create($storeId)->generate($params);
        }
        return $this->clientToken;
    }

    /**
     * Get Icon function
     *
     * @return array|string
     */
    public function getIcons()
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $types = $this->ccConfig->getCcAvailableTypes();
        foreach ($types as $code => $label) {
            if (!array_key_exists($code, $this->icons)) {
                $asset = $this->ccConfig->createAsset('Magento_Payment::images/cc/' . strtolower($code) . '.png');
                $placeholder = $this->assetSource->findSource($asset);
                if ($placeholder) {
                    list($width, $height) = getimagesize($asset->getSourceFile());
                    $this->icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => $width,
                        'height' => $height,
                        'title' => __($label),
                    ];
                }
            }
        }

        return $this->_jsonEncoder->encode($this->icons);
    }

    /**
     * Get Braintree Config
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function getBraintreeConfig()
    {
        $storeId = $this->session->getStoreId();
        $config = [
          'ccTypesMapper' => $this->config->getCcTypesMapper(),
          'countrySpecificCardTypes' => $this->config->getCountrySpecificCardTypeConfig($storeId),
          'availableCardTypes' => $this->config->getAvailableCardTypes($storeId),
        ];
        return $this->_jsonEncoder->encode($config);
    }
}
