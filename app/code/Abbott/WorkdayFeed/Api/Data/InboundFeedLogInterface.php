<?php

namespace Abbott\WorkdayFeed\Api\Data;

interface InboundFeedLogInterface
{
    public const ROW_ID ='row_id';
    public const UPI ='upi';
    public const RECORD_STATUS ='record_status';
    public const RECORD ='record';
    public const STATUS ='status';
    public const MESSAGE ='message';
    public const FEED_ID ='feed_id';
    public const CREATED_AT ='created_at';
    public const UPDATED_AT ='updated_at';

    /**
     * Get RowId
     *
     * @return int|null
     */
    public function getRowId(): ?int;
    /**
     * Get Upi
     *
     * @return int|null
     */
    public function getUpi(): ?int;
    /**
     * Get RecordStatus
     *
     * @return string|null
     */
    public function getRecordStatus(): ?string;
    /**
     * Get Record
     *
     * @return string|null
     */
    public function getRecord(): ?string;
    /**
     * Get Status
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
     * Get FeedId
     *
     * @return int|null
     */
    public function getFeedId(): ?int;
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
     * Set RowId
     *
     * @param string $rowId
     * @return InboundFeedLogInterface
     */
    public function setRowId(string $rowId): InboundFeedLogInterface;
    /**
     * Set Upi
     *
     * @param string $upi
     * @return InboundFeedLogInterface
     */
    public function setUpi(string $upi): InboundFeedLogInterface;
    /**
     * Set Data
     *
     * @param string $recordStatus
     * @return InboundFeedLogInterface
     */
    public function setRecordStatus(string $recordStatus): InboundFeedLogInterface;
    /**
     * Set Record
     *
     * @param string $record
     * @return InboundFeedLogInterface
     */
    public function setRecord(string $record): InboundFeedLogInterface;

    /**
     * Set Status
     *
     * @param string $status
     * @return InboundFeedLogInterface
     */
    public function setStatus(string $status): InboundFeedLogInterface;
    /**
     * Set Message
     *
     * @param string $message
     * @return InboundFeedLogInterface
     */
    public function setMessage(string $message): InboundFeedLogInterface;
    /**
     * Set FeedId
     *
     * @param int $feedId
     * @return \Abbott\WorkdayFeed\InboundFeedLogInterface
     */
    public function setFeedId(int $feedId): \Abbott\WorkdayFeed\InboundFeedLogInterface;
    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return InboundFeedLogInterface
     */
    public function setCreatedAt(string $createdAt): InboundFeedLogInterface;
    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return InboundFeedLogInterface
     */
    public function setUpdatedAt(string $updatedAt): InboundFeedLogInterface;
}
