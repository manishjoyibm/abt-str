<?php

namespace Abbott\WorkdayFeed\Block\Adminhtml\InboundFeed\Edit;

use Magento\Backend\Block\Widget\Context;
use Abbott\WorkdayFeed\Api\InboundFeedRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class GenericButton
{
    public $logger;
    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var InboundFeedRepositoryInterface
     */
    protected InboundFeedRepositoryInterface $inboundFeedRepository;

    /**
     * @param LoggerInterface $logger
     * @param Context $context
     * @param InboundFeedRepositoryInterface $inboundFeedRepository
     */
    public function __construct(
        LoggerInterface $logger,
        Context $context,
        InboundFeedRepositoryInterface $inboundFeedRepository
    ) {
        $this->logger = $logger;
        $this->context = $context;
        $this->inboundFeedRepository = $inboundFeedRepository;
    }

    /**
     * Return InboundFeed ID
     *
     * @return int|null
     * @throws LocalizedException
     */
    public function getRowId(): ?int
    {
        try {
            return $this->inboundFeedRepository->getById(
                $this->context->getRequest()->getParam('row_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
              $this->logger->critical($e->getMessage());
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
