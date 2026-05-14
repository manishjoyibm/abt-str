<?php

namespace Abbott\MyAccount\Model\Resolver;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Abbott\MyAccount\Controller\Account\LoginPost;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Exception\LocalizedException;
use Abbott\MyAccount\Helper\Data as AccountHelper;

class GenerateCustomerToken extends \Magento\CustomerGraphQl\Model\Resolver\GenerateCustomerToken
{

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected $loginPost;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;


    protected $accountHelper;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;


    /**
     * @param CustomerTokenServiceInterface $customerTokenService
     */
    public function __construct(
        CustomerTokenServiceInterface $customerTokenService, LoginPost $loginPost, AccountManagementInterface $customerAccountManagement, AccountHelper $accountHelper,
    ) {
        $this->customerTokenService = $customerTokenService;
        $this->loginPost = $loginPost;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->accountHelper = $accountHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Specify the "email" value.'));
        }

        if (empty($args['password'])) {
            throw new GraphQlInputException(__('Specify the "password" value.'));
        }

        try {
            $token = $this->customerTokenService->createCustomerAccessToken($args['email'], $args['password']);
            $customer = $this->customerAccountManagement->authenticate($args['email'], $args['password']);
            $this->loginPost->setCustomerInfomation($token, $customer);
            return ['token' => $token];
        } catch (UserLockedException $e) {
            $message = __(
                'Your account is locked. Please wait and try again later.'
            );
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        } catch (AuthenticationException $e) {
            $message = __(
                'Email Address or Password is Invalid.'
            );
            throw new GraphQlAuthenticationException(__($message));
        } catch (LocalizedException $e) {
            $message = $e->getMessage();
        } catch (\Exception $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        } finally {
            if (isset($message)) {
                $redirectUrl = $this->getCookieManager()->getCookie('redirectUrl');
                if ($redirectUrl) {
                    $cookieDomain = $this->accountHelper->getCookieRedirect();
                    $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();
                    $publicCookieMetadata->setPath('/');
                    $publicCookieMetadata->setDomain($cookieDomain);
                    $publicCookieMetadata->setHttpOnly(false);
                    $publicCookieMetadata->setSecure(true);
                }
            }
        }
    }


    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
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
}
