<?php


namespace Abbott\GPAS\Model\Cookie;

use Abbott\MyAccount\Helper\Data;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;

class QrCode
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'qrcode';

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;
    /**
     * @var Http
     */
    private $request;
    /**
     * @var Data
     */
    private $accountHelper;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     * @param Data $accountHelper
     * @param Http $request
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        Data $accountHelper,
        Http $request
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->request = $request;
        $this->accountHelper = $accountHelper;
    }

    /**
     * Get form key cookie
     *
     * @return string
     */
    public function get()
    {
        return $this->cookieManager->getCookie(self::COOKIE_NAME);
    }

    /**
     * Set function
     *
     * @param string $value
     * @param int $duration
     * @return void
     */
    public function set($value, $duration = 86400)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setSecure($this->request->isSecure())
            ->setHttpOnly(false)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->accountHelper->getCookieRedirect());

        $this->cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $value,
            $metadata
        );
    }

    /**
     * Delete function
     *
     * @return void
     */
    public function delete()
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setSecure($this->request->isSecure())
            ->setHttpOnly(false)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->accountHelper->getCookieRedirect());

        $this->cookieManager->deleteCookie(
            self::COOKIE_NAME,
            $metadata
        );
    }
}
