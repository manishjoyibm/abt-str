<?php

declare(strict_types=1);

namespace Abbott\MyAccount\Plugin;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ReCaptchaValidationApi\Api\ValidatorInterface;
use Magento\ReCaptchaWebapiApi\Api\WebapiValidationConfigProviderInterface;
use Magento\ReCaptchaWebapiApi\Model\Data\EndpointFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Validate ReCaptcha for GraphQl mutations.
 */
class GraphQlValidator
{

    const IS_AEM_LOGIN_USER_KEY = 'recaptcha_frontend/type_for/isaemloginuserkey';


    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var WebapiValidationConfigProviderInterface
     */
    private $configProvider;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EndpointFactory
     */
    private $endpointFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param HttpRequest $request
     * @param WebapiValidationConfigProviderInterface $configProvider
     * @param ValidatorInterface $validator
     * @param EndpointFactory $endpointFactory
     */
    public function __construct(
        HttpRequest $request,
        WebapiValidationConfigProviderInterface $configProvider,
        ValidatorInterface $validator,
        EndpointFactory $endpointFactory,
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->request = $request;
        $this->configProvider = $configProvider;
        $this->validator = $validator;
        $this->endpointFactory = $endpointFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Validate ReCaptcha for mutations if needed.
     *
     * @param ResolverInterface $subject
     * @param Field $fieldInfo
     * @param mixed $context
     * @param ResolveInfo $resolveInfo
     * @throws GraphQlInputException
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        ResolverInterface $subject,
        Field $fieldInfo,
        $context,
        ResolveInfo $resolveInfo,
        array $value = null,
        array $args = null
    ): void {
        if ($resolveInfo->operation->operation !== 'mutation') {
            return;
        }
        $isAEMLoginUserKey = trim((string)$this->scopeConfig->getValue(self::IS_AEM_LOGIN_USER_KEY));
        $reCaptchaConfig = $this->configProvider->getConfigFor(
            $this->endpointFactory->create([
                'class' => ltrim($fieldInfo->getResolver(), '\\'),
                'method' => 'resolve',
                'name' => $fieldInfo->getName()
            ])
        );
        $validateRecaptcha = true;
        if (($fieldInfo->getName() == 'generateCustomerToken') && isset($args['is_aem_login_user_key']) && ($args['is_aem_login_user_key'] == $isAEMLoginUserKey)) {
            $validateRecaptcha = false;
        }
        if ($validateRecaptcha) {
            if (
                $reCaptchaConfig
                && !$this->validator->isValid(
                    (string)$this->request->getHeader('X-ReCaptcha'),
                    $reCaptchaConfig
                )->isValid()
            ) {
                throw new GraphQlInputException(__('ReCaptcha validation failed, please try again'));
            }
        }
    }
}
