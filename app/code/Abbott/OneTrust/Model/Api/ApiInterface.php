<?php
/**
 * =================================================================
 * This package designed for Magento Enterprise edition
 * =================================================================
 *
 * @category  Abbott
 * @package   Abbott_OneTrust
 * @author    Vinay Singh <vinay.singh3@abbott.com>
 * @copyright 2023 Abbott
 * @license   Abbott abbott.com
 * @link      <abbott.com>
 */
namespace Abbott\OneTrust\Model\Api;

/**
 * Class  Api
 * Abbott\Model\Api
 *
 * @copyright 2023 Abbott
 * @license   Abbott abbott.com
 * @link      <abbott.com>
 */
class ApiInterface
{
    public const MODULE_ENABLED = 'onetrust/general/enabled';

    public const ONETRUST_TOKEN = 'onetrust/general/oauth_token';

    public const COLLECTION_POINT_URL = 'onetrust/general/get_collection_end_point';

    public const GET_VERBIAGE_CONSENTS = 'get_verbiage_consents';
    public const POST_VERBIAGE_CONSENTS = 'post_verbiage_consents';

    public const REGISTRATION_COLLECTION_POINTS_ID =
        'onetrust/environment_configuration/registration_collection_point_id';

    public const CHECKOUT_COLLECTION_POINT_ID =
        'onetrust/checkout_environment_configuration/checkout_collection_point_id';
    public const CHECKOUT_EMP_PURPOSE_ID = 'onetrust/checkout_environment_configuration/checkout_employee_term';
    public const CHECKOUT_PAYMENT_PURPOSE_ID = 'onetrust/checkout_environment_configuration/checkout_payment_term';

    public const USE_ENVIRONMENT = 0;
    public const WRONG_ENVIRONMENT = 3;

    public const SUCCESS = 200;
    public const STATUS = 'status';
    public const REASON = 'reason';

    public const ONE_VALUE = 1;
    public const ZERO_VALUE = 0;

    public const CACHE_KEY  = \Abbott\OneTrust\Model\Cache\Type\CacheType::TYPE_IDENTIFIER;
    public const CACHE_TAG  = \Abbott\OneTrust\Model\Cache\Type\CacheType::CACHE_TAG;

    public const JWT_TOKEN  = 'onetrust/environment_configuration/jwt_token';
    public const MBO_JWT_TOKEN  = 'onetrust/mbo_environment_configuration/mbo_jwt_token';
    public const CHECKOUT_JWT_TOKEN  = 'onetrust/checkout_environment_configuration/checkout_jwt_token';

    public const NEWSLETTER_PURPOSE_ID  = 'onetrust/environment_configuration/newsletter_purpose_id';
    public const MBO_NEWSLETTER_PURPOSE_ID  = 'onetrust/mbo_environment_configuration/mbo_newsletter_purpose_id';

    public const NEWSLETTER_NOTICE_ID  = 'onetrust/environment_configuration/newsletter_notice_id';
    public const CHECKOUT_EMP_NOTICE_ID  = 'onetrust/checkout_environment_configuration/emp_consent_notice_id';
    public const CHECKOUT_PAYMENT_NOTICE_ID  = 'onetrust/checkout_environment_configuration/payment_consent_notice_id';

    public const IS_MODULE_ENABLE = 'is_module_enable';
    public const CACHE_TIME = 'onetrust/general/cache_valid_time';

    public const ACCEPT = 'Accept';
    public const APPLICATION_JSON = 'application/json';
    public const CONTENT_TYPE = 'Content-Type';
    public const AUTHORIZATION = 'Authorization';
    public const BEARER = 'Bearer';

    public const POST_CONSENT_POINT_URL = 'onetrust/general/submit_consent_end_point';
    public const IDENTIFIER = 'identifier';
    public const BODY = 'body';
    public const REQUEST_INFO = 'requestInformation';
    public const PURPOSE = 'purposes';
    public const TRANSACTION_TYPE = 'TransactionType';
    public const CONFIRMED = 'CONFIRMED';
    public const WITHDRAWN = 'WITHDRAWN';
    public const ID = 'Id';
    public const NOTGIVEN = 'NOTGIVEN';
}
