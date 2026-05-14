<?php

namespace Abbott\Sarp2\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use \Abbott\Sarp2\Helper\Data;

class SkipButton implements ButtonProviderInterface
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
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        ProfileManagementInterface $profileManagement,
        Data $helper
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->profileManagement = $profileManagement;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $csrUser = $this->helper->isCsrUser();
        $data = [];
        $profileId = $this->request->getParam('profile_id');
        /* Check if not a CSR user */
        if ($profileId && empty($csrUser)) {
            $allowedStatuses = $this->profileManagement->getAllowedStatuses($profileId);
            if (in_array(Status::PAUSE, $allowedStatuses)) {
                $data = [
                    'label' => __('Skip'),
                    'class' => 'save',
                    'on_click' => sprintf(
                        "deleteConfirm('%s', '%s')",
                        __('Are you sure you want to do this?'),
                        $this->urlBuilder->getUrl('*/*/skip', ['profile_id' => $profileId])
                    ),
                    'sort_order' => 40,
                ];
            }
        }
        return $data;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
