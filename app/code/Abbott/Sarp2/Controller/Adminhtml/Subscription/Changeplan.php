<?php
namespace Abbott\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Changeplan extends Action
{
    public $authorization;
    /**
     * @var \Abbott\AwsLambda\Logger\Log
     */
    public $log;
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProfileRepositoryInterface $profileRepository,
        \Abbott\AwsLambda\Logger\Log $log,
        \Magento\Framework\AuthorizationInterface $authorization
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->profileRepository = $profileRepository;
        $this->authorization = $authorization;
        $this->log = $log;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
            $profileId = (int)$this->getRequest()->getParam('profile_id');
            if ($profileId) {
                try {
                    $profile = $this->profileRepository->get($profileId);
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage
                        ->setActiveMenu('Aheadworks_Sarp2::subscriptions')
                        ->getConfig()->getTitle()->prepend(
                            '#' . $profile->getIncrementId()
                        );
                    return $resultPage;
                } catch (NoSuchEntityException $exception) {
                    $this->messageManager->addExceptionMessage(
                        $exception,
                        __('Something went wrong while open the profile page.')
                    );
                }
            }
    }
    
    /**
     * Added resource for access
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
