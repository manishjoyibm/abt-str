<?php


namespace Abbott\Sarp2\Plugin\Model\Profile\View\Edit\Payment;


// use MSP\ReCaptcha\Model\LayoutSettings;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\LayoutProcessor;

/**
 * Class LayoutProcessorPlugin
 * @package Abbott\Sarp2\Plugin\Model\Profile\View\Edit\Payment
 */
class LayoutProcessorPlugin
{

    /**
     * @var LayoutSettings
     */
    private $layoutSettings;

    /**
     * LayoutProcessorPlugin constructor.
     * @param LayoutSettings $layoutSettings
     */
    public function __construct(//LayoutSettings $layoutSettings
        ){

        // $this->layoutSettings = $layoutSettings;
    }

    public function afterProcess(LayoutProcessor $subject, $jsLayout) {
        if (isset($jsLayout['components']['payment']['children']['payments-list']['children'])
        ) {
            // $jsLayout['components']['payment']['children']['payments-list']['children']['msp_recaptcha']['settings'] = $this->layoutSettings->getCaptchaSettings();
        }
        return $jsLayout;
    }

}
