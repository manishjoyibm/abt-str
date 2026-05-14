<?php

namespace Abbott\Sarp2\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use \Abbott\Sarp2\Helper\Data;

class PauseButton implements ButtonProviderInterface
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

    protected $profileFactory;
    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        ProfileManagementInterface $profileManagement,
        ProfileInterfaceFactory $profileFactory,
        Data $helper
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->profileManagement = $profileManagement;
        $this->profileFactory = $profileFactory;
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
        $profileData = $this->profileFactory->create()->load($profileId);
        $allowedStatuses = $this->profileManagement->getAllowedStatuses($profileId);
        /* Check if not a CSR user */
        if(empty($csrUser)) {
            if ($profileId && $profileData->getStatus() == Status::PAUSE) {
                $data = [
                    'label' => __('Resume'),
                    'class' => 'save',
                    'on_click' => sprintf(
                        "deleteConfirm('%s', '%s')",
                        __('Are you sure you want to do this?'),
                        $this->urlBuilder->getUrl('*/*/resume', ['profile_id' => $profileId])
                    ),
                    'sort_order' => 40,
                ];
            } else {
                $data = [
                    'label' => __('Pause'),
                    'class' => 'save',
                    'on_click' => sprintf(
                        "deleteConfirm('%s', '%s')",
                        __('Are you sure you want to do this?'),
                        $this->urlBuilder->getUrl('*/*/pause', ['profile_id' => $profileId])
                    ),
                    'sort_order' => 40,
                ];
            }
        }
            return $data;
        //}
    }

    protected function _isAllowed()
    {
        return true;
    }
}
