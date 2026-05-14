<?php


namespace Abbott\Sarp2\Plugin\Block\Customer;


use Aheadworks\Sarp2\Block\Customer\Subscription;
use \Magento\Framework\Data\Form\FormKey;
class SubscriptionPlugin
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * SubscriptionBeforePlugin constructor.
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param FormKey $formKey
     */
    public function __construct(\Magento\Framework\UrlInterface $urlBuilder, FormKey $formKey) {

        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
    }
    /**
     * Get cancel profile url
     *
     * @param int $profileId
     * @return string
     */
    public function afterGetCancelUrl(Subscription $subject, $url, $profileId)
    {
        return $this->urlBuilder->getUrl(
            'aw_sarp2/profile/cancel',
            ['profile_id' => $profileId, 'form_key' => $this->formKey->getFormKey()]
        );
    }
}
