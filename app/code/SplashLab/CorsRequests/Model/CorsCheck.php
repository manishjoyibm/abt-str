<?php
/**
 * @copyright  Copyright 2017 SplashLab
 */

namespace SplashLab\CorsRequests\Model;

use SplashLab\CorsRequests\Api\CorsCheckInterface;

/**
 * Class CorsCheck
 * @package SplashLab\CorsRequests\Model
 */
class CorsCheck implements CorsCheckInterface
{

    public $response;
    public $request;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Webapi\Rest\Response $response
     * @param \Magento\Framework\App\Config\ScopeConfigInterface scopeConfig
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Response $response,
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function check()
    {
        // respond to OPTIONS request with appropriate headers
        $this->response->setHeader('Access-Control-Allow-Methods', $this->request->getHeader('Access-Control-Request-Method'), true);
        $this->response->setHeader('Access-Control-Allow-Headers', $this->request->getHeader('Access-Control-Request-Headers'), true);
        $this->response->setHeader('Access-Control-Expose-Headers', $this->request->getHeader('Access-Control-Expose-Headers'), true);
        return '';
    }

}
