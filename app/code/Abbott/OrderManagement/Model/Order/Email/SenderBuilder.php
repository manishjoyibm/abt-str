<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Abbott\OrderManagement\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var IdentityInterface
     */
    protected $identityContainer;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

     /**
      * @var LoggerInterface
      */
    protected $logger;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param TransportBuilderByStore $transportBuilderByStore
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        parent::__construct($templateContainer, $identityContainer, $transportBuilder, $transportBuilderByStore);
        $this->logger = $logger;
    }

    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function send()
    {
        try {
            $alternateEmail ='';
            $this->configureEmailTemplate();

            if ($amastyOrderAttr = $this->templateContainer->getTemplateVars()
                ['order']->getExtensionAttributes()->getAmastyOrderAttributes()
            ) {
                foreach ($amastyOrderAttr as $data) {
                    $mainData = $data->__toArray();
                    if ($mainData['attribute_code'] == 'additional_email') {
                        $alternateEmail = $mainData['value'];
                    }
                }
            }

            $this->transportBuilder->addTo(
                $this->identityContainer->getCustomerEmail(),
                $this->identityContainer->getCustomerName()
            );

            $copyTo = $this->identityContainer->getEmailCopyTo();

            if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
                foreach ($copyTo as $email) {
                    $this->transportBuilder->addBcc($email);
                }
            }

            if ($alternateEmail) {
                $this->transportBuilder->addBcc($alternateEmail);
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
