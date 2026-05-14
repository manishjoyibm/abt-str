<?php
namespace Abbott\CustomerTwoFactorAuth\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var PsrLogger
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param PsrLogger $logger
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        PsrLogger $logger
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param $sendersName
     * @param $sendersEmail
     * @param $otp
     * @param $receiverEmail
     * @param $templateId
     * @return bool
     */
    public function sendEmail(
        $sendersName,
        $sendersEmail,
        $otp,
        $receiverEmail,
        $templateId
    ) {
        try {
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($sendersName),
                'email' => $this->escaper->escapeHtml($sendersEmail),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars([
                    'otp'  => $otp,
                    'store_name' => $this->storeManager->getStore()->getName()
                ])
                ->setFromByScope(
                    $sender,
                    $this->storeManager->getStore()->getId()
                )->addTo(
                    $receiverEmail
                )->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        return true;
    }
}
