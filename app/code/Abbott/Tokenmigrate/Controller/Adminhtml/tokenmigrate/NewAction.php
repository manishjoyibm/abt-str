<?php
namespace Abbott\Tokenmigrate\Controller\Adminhtml\Tokenmigrate;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\Collection;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var Forward
     */
    protected $resultForwardFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param EncryptorInterface $encryptor
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        CollectionFactory $collectionFactory,
        EncryptorInterface $encryptor
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->encryptor = $encryptor;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Return to index after Script run
     *
     * @return Forward
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $collectionSize = $collection->getSize();
        if ($collectionSize>0) {
            foreach ($collection as $vaultVal) {
                $publicHashValue = $vaultVal->getGatewayToken();
                if ($vaultVal->getCustomerId()) {
                    $publicHashValue = $vaultVal->getCustomerId();
                }
                $publicHashValue .= $vaultVal->getPaymentMethodCode();
                $publicHashValue .= $vaultVal->getType();
                $publicHashValue .= $vaultVal->getDetails();
                $hashKey = $this->encryptor->getHash($publicHashValue);
                /* Save new Hash key value in public hash */
                $vaultRow = $vaultVal->setPublicHash($hashKey);
                $vaultRow->save();
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addSuccess(__('Script run successfully.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
