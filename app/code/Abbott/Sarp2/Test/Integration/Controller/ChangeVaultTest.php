<?php
namespace Abbott\Sarp2\Test\Integration\Controller;

use Abbott\Sarp2\Controller\Subscription\ChangeVault;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface as AheadworksInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ChangeVaultTest extends TestCase
{

    public $objectManager;
    public $changevault;
    public $profileInterface;
    public $paymentTokenFactory;
    public $paymentTokenRepository;
    public $paymentsList;
    public $paymentPersistence;
    public $searchCriteriaBuilder;
    public $aheadworksVault;
    public $json;
    const PROFILE_ID = 33630;
    const VAULT_ID = 19238;
    const CUSTOMER_ID = 283519;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->changevault = $this->objectManager->create(ChangeVault::class);
        $this->profileInterface = $this->objectManager->create(ProfileRepositoryInterface::class);
        $this->paymentTokenFactory = $this->objectManager->create(PaymentTokenInterfaceFactory::class);
        $this->paymentTokenRepository = $this->objectManager->create(PaymentTokenRepositoryInterface::class);
        $this->paymentsList = $this->objectManager->create(PaymentsList::class);
        $this->paymentPersistence = $this->objectManager->create(Persistence::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $this->aheadworksVault = $this->objectManager->create(AheadworksInterface::class);
        $this->json = $this->objectManager->create(Json::class);
    }

    /**
     * Destroy Object
     */
    protected function tearDown()
    {
        $this->objectManager = null;
        $this->changevault = null;
        $this->profileInterface = null;
        $this->paymentTokenFactory = null;
        $this->paymentTokenRepository = null;
        $this->paymentsList = null;
        $this->paymentPersistence = null;
        $this->searchCriteriaBuilder = null;
        $this->json = null;

    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testcheckCustomerProfile()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(ProfileInterface::CUSTOMER_ID, self::CUSTOMER_ID, 'in')->addFilter(ProfileInterface::PROFILE_ID, self::PROFILE_ID, 'in')->create();
        $profiles = $this->profileInterface->getList($searchCriteria)->getItems();
        $this->assertGreaterThan(0, count($profiles));
    }

    public function testsaveAwSarp2Token()
    {

        $awSarp2TokenId = 0;
        $profile = $this->profileInterface->get(self::PROFILE_ID);
        $paymentData = $this->paymentTokenRepository->getById(self::VAULT_ID);
        if ($paymentData) {
            $details = $this->json->unserialize($paymentData->getDetails());
            $type = isset($details['type']) ? $details['type'] : null;
            $maskedCC = isset($details['maskedCC']) ? $details['maskedCC'] : null;
            $expirationDate = isset($details['expirationDate']) ? $details['expirationDate'] : null;
            $tokenType = $paymentData->getType();
            $paymentMethod = $paymentData->getPaymentMethodCode();
            $paymentToken = $this->paymentTokenFactory->create();
            $paymentToken->setPaymentMethod($paymentMethod)
                ->setType($tokenType)
                ->setTokenValue($paymentData->getGatewayToken())
                ->setIsActive(true);
            $paymentToken->setExpiresAt($paymentData->getExpiresAt())->setDetails(
                'type',
                $type
            )->setDetails(
                'maskedCC',
                $maskedCC
            )->setDetails(
                'expirationDate',
                $expirationDate
            );
            $this->aheadworksVault->save($paymentToken);
            $awSarp2TokenId = $paymentToken->getId();
            if ($awSarp2TokenId > 0) {
                $profile->setPaymentTokenId($awSarp2TokenId);
                $profile->setPaymentMethod($paymentData->getPaymentMethodCode());
                $payments = $this->paymentsList->getLastScheduled(self::PROFILE_ID);
                foreach ($payments as $payment) {
                    $paymentData = $payment->getPaymentData();
                    $paymentData['token_id'] = $profile->getPaymentTokenId();
                    $payment->setPaymentData($paymentData);
                }
                if (is_array($payments) && count($payments)) {
                    $this->paymentPersistence->massSave($payments);
                }
            }
        }
        $this->assertGreaterThan(0, $awSarp2TokenId);

    }

}
