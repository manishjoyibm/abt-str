<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Response\RedirectInterface;

/**
 * Class BackLink
 *
 * @method BackLink setRefererUrl(string $refererUrl)
 * @method BackLink setTitle(string $title)
 * @method BackLink setActionClass(string $actionClass)
 * @method string getRefererUrl()
 * @method string getTitle()
 * @method string getActionClass()
 * @method string|null getDisplayIfParamInUrl()
 * @package Aheadworks\Sarp2\Block
 */
class BackLink extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::back_link.phtml';

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @param Context $context
     * @param RedirectInterface $redirect
     * @param array $data
     */
    public function __construct(
        Context $context,
        RedirectInterface $redirect,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->redirect = $redirect;
    }

    /**
     * Get back Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // The RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }

        return $this->redirect->getRefererUrl();
    }

    /**
     * Check is display button or not
     *
     * @return bool
     */
    public function isDisplay()
    {
        if ($this->getDisplayIfParamInUrl()) {
            return $this->_request->getParam($this->getDisplayIfParamInUrl(), false);
        }

        return true;
    }
}
