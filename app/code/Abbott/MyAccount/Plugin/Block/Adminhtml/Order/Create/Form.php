<?php
namespace Abbott\MyAccount\Plugin\Block\Adminhtml\Order\Create;

use Abbott\MyAccount\Helper\Data;
use Abbott\MyAccount\Helper\LinkData;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;

class Form
{
    public $_jsonEncoder;
    public $_jsonDecoder;
    /**
     * @var \Abbott\MyAccount\Helper\Data
     */
    public $_helper;
    /**
     * @var \Abbott\MyAccount\Helper\LinkData
     */
    public $_linkdata;
    /**
     * Construct function
     *
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     * @param Data $helper
     * @param LinkData $linkdata
     */
    public function __construct(
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        Data $helper,
        LinkData $linkdata
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_helper = $helper;
        $this->_linkdata = $linkdata;
    }

    /**
     * Return array of user defined attributes
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Form $subject
     * @param $result
     * @return string
     */
    public function aftergetOrderDataJson(\Magento\Sales\Block\Adminhtml\Order\Create\Form $subject, $result)
    {
        $data = $this->_jsonDecoder->decode($result);
        $data['flagstore'] = $this->_linkdata->getEmailEditDisableFlag($this->_helper->getNewSimilacStoreId());
        $data['similac_store_id'] = $this->_helper->getNewSimilacStoreId();
        return $this->_jsonEncoder->encode($data);
    }
}
