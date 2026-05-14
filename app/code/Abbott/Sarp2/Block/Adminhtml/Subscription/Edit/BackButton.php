<?php
 

namespace Abbott\Sarp2\Block\Adminhtml\Subscription\Edit;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface; 
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\App\RequestInterface;

 
class BackButton implements ButtonProviderInterface
{
     /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;
      
    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder     
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
      
    }


    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $profileId = $this->request->getParam('profile_id');
        return [
            'label' => __('Back'),
            'on_click' => sprintf(
                "deleteConfirm('%s', '%s')",
                __('Are you sure you want to go back?'),
                $this->urlBuilder->getUrl('*/*/view', ['profile_id' => $profileId])
            ),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
