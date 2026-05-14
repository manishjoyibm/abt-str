<?php
namespace Abbott\CustomerTwoFactorAuth\Block\Button;

use Abbott\CustomerTwoFactorAuth\Helper\Data;

use Magento\Framework\Data\Form\FormKey;


class Login extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    protected $helper;

    
    private $formKey;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Abbott\CustomerTwoFactorAuth\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        FormKey $formKey,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Label print
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __('Please verify your account');
    }

    /**
     * Get ending time
     *
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->helper->getExpiryLimit();
    }

    
    public function getFormKey(): string
        {
            return $this->formKey->getFormKey();
        }

}
