<?php

namespace Abbott\Strongmoms\Block;

use Abbott\Strongmoms\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

class Checkssmuser extends \Magento\Framework\View\Element\Template
{

     /**
      * @var UrlInterface
      */
    public $urlBuilder;
    /**
     * @var Data
     */
    protected $helper;

    protected $ssmUserNotes;
    protected $susbscriptionUserNote;

    /**
     * Construct function
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }

     /**
      * Check customer is ssm and any order placed or not
      *
      * @return boolean
      */
    public function checkUserIsSsm()
    {
        return $this->helper->isSSM(); // if true
    }

     /**
      * Any order placed or not
      *
      * @return boolean
      */
    public function getOrderCount()
    {
        $ssmCustomerExistingOrder = false;
        $ssmCustomerOrderCount = $this->helper->getSsmUserOrderCount();
        if ($ssmCustomerOrderCount > 0) {
            $ssmCustomerExistingOrder = true;
        }
        return $ssmCustomerExistingOrder;
    }

    /**
     * Get ssm user Notes from backend system
     *
     * @return string
     */
    public function getSsmUserNotes()
    {
        $ssmUserNote = trim($this->helper->getSsmUserConfig());
        if (isset($ssmUserNote) && $ssmUserNote !== ""):
            $this->ssmUserNotes = $ssmUserNote;
        endif;
        return $this->ssmUserNotes;
    }

    /**
     * Get subscription user Notes from backend system
     *
     * @return string
     */
    public function getSubscriptionUserNotes()
    {
        $susbscriptionUserNote = trim($this->helper->getsubscriptionUserConfig());
        if (isset($susbscriptionUserNote) && $susbscriptionUserNote !== "" && $this->helper->getuserSubscription()):
            $this->susbscriptionUserNote = $susbscriptionUserNote;
        endif;
        return $this->susbscriptionUserNote;
    }
}
