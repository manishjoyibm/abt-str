<?php

namespace Abbott\WorkdayFeed\Api\Data;

interface InboundFeedInterface
{
    public const FEED_ID ='feed_id';
    public const FILE_NAME ='file_name';
    public const CREATED_AT ='created_at';
    public const UPDATED_AT ='updated_at';
    public const STATUS ='status';
    public const MESSAGE ='message';
    public const TYPE ='type';
    /**
     * Get FeedId
     *
     * @return int|null
     */
    public function getFeedId(): ?int;
    /**
     * Get FileName
     *
     * @return string|null
     */
    public function getFileName(): ?string;

    /**
     * Get CreatedAt
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;
    /**
     * Get UpdatedAt
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;
    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMessage(): ?string;
    /**
     * Get Type
     *
     * @return string|null
     */
    public function getType(): ?string;
    /**
     * Set FeedId
     *
     * @param int $feedId
     * @return \Abbott\WorkdayFeed\InboundFeedInterface
     */
    public function setFeedId(int $feedId): \Abbott\WorkdayFeed\InboundFeedInterface;
    /**
     * Set FileName
     *
     * @param string $fileName
     * @return InboundFeedInterface
     */
    public function setFileName(string $fileName): InboundFeedInterface;

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return InboundFeedInterface
     */
    public function setCreatedAt(string $createdAt): InboundFeedInterface;
    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return InboundFeedInterface
     */
    public function setUpdatedAt(string $updatedAt): InboundFeedInterface;
    /**
     * Set Status
     *
     * @param string $status
     * @return InboundFeedInterface
     */
    public function setStatus(string $status): InboundFeedInterface;
    /**
     * Set Message
     *
     * @param string $message
     * @return InboundFeedInterface
     */
    public function setMessage(string $message): InboundFeedInterface;
    /**
     * Set Type
     *
     * @param string $type
     * @return InboundFeedInterface
     */
    public function setType(string $type): InboundFeedInterface;
}
