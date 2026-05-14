<?php
namespace Abbott\Braintree\Plugin\Gateway\Config\PayPal;

use Magento\Payment\Model\CcConfig;

class Config
{

    /**
     * @var CcConfig
     */
    private $ccConfig;

     /**
     * @var array
     */
    private $icon = [];

    public function __construct(CcConfig $ccConfig)
    {
        $this->ccConfig = $ccConfig;
    }
    public function afterGetPayPalIcon(\PayPal\Braintree\Gateway\Config\PayPal\Config $subject, $result): array
    {
        if (empty($this->icon)) {
            $asset = $this->ccConfig->createAsset('PayPal_Braintree::images/paypal.png');
            // changes getimagesizefromstring to getimagesize.
            list($width, $height) = getimagesize($asset->getSourceFile());
            $this->icon = [
                'url' => $asset->getUrl(),
                'width' => $width,
                'height' => $height
            ];
        }

        return $this->icon;
    }
}
