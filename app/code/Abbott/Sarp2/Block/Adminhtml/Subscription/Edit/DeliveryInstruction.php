<?php


namespace Abbott\Sarp2\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Backend\Block\Template;

class DeliveryInstruction extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Abbott_Sarp2::subscription/edit/delivery_instruction.phtml';

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    public function getDeliveryInstruction(){
        return $this->getProfile()->getDeliveryInstruction();
    }
}
