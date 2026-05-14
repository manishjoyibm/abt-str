<?php


namespace Abbott\GPAS\Plugin\Model;


use Abbott\GPAS\Api\QrCodeRepositoryInterface;
use Abbott\GPAS\Helper\Data;
use Abbott\GPAS\Logger\Logger;
use Abbott\GPAS\Model\Attribute\Customer\QrCodeId;
use Abbott\GPAS\Model\Cookie\QrCode;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AccountManagementPlugin
 * @package Abbott\GPAS\Plugin\Model
 */
class AccountManagementPlugin
{
    /**
     * @var QrCode
     */
    private $qrCodeCookie;
    /**
     * @var QrCodeRepositoryInterface
     */
    private $qrCodeRepository;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var Data
     */
    private $data;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * AccountManagementPlugin constructor.
     * @param QrCodeRepositoryInterface $qrCodeRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param AppState $appState
     * @param QrCode $qrCodeCookie
     * @param Logger $logger
     * @param Data $data
     */
    public function __construct(
        QrCodeRepositoryInterface $qrCodeRepository,
        CustomerRepositoryInterface $customerRepository,
        AppState $appState,
        QrCode $qrCodeCookie,
        Logger $logger,
        Data $data, StoreManagerInterface $storeManager
    ) {

        $this->qrCodeCookie = $qrCodeCookie;
        $this->qrCodeRepository = $qrCodeRepository;
        $this->customerRepository = $customerRepository;
        $this->data = $data;
        $this->logger = $logger;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
    }

    /**
     * @param AccountManagementInterface $subject
     * @param CustomerInterface $customer
     */
    public function afterAuthenticate(AccountManagementInterface $subject, $customer) {
        $this->assignQrCodeToCustomer($customer);
        return $customer;
    }

    /**
     * @param AccountManagementInterface $subject
     * @param CustomerInterface $customer
     */
    public function beforeCreateAccount(AccountManagementInterface $subject, $customer, $password = null, $redirectUrl = '') {
        $this->assignQrCodeToCustomer($customer, false);
        return [$customer, $password, $redirectUrl];
    }


    /**
     * @param CustomerInterface $customer
     */
    protected function assignQrCodeToCustomer($customer, $save = true) {
        try {
            if($this->data->isEnabled($this->storeManager->getStore()->getId()) && !$this->isAdminScope()) {
                if ($code = $this->qrCodeCookie->get()) {
                    $qrCode = $this->qrCodeRepository->getByCode($code);
                    $customer->setCustomAttribute(QrCodeId::ATTRIBUTE_CODE, $qrCode->getId());
                    if($save){
                        $this->customerRepository->save($customer);
                    }
                    $this->logger->info(__(sprintf("Customer %d logged in. Code assigned %s", $customer->getId(), $this->qrCodeCookie->get())));
                    $this->qrCodeCookie->delete();
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isAdminScope() {
        return $this->appState->getAreaCode() == Area::AREA_ADMINHTML;
    }
}
