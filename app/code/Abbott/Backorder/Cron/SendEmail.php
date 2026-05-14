<?php
namespace Abbott\Backorder\Cron;

use Abbott\Backorder\Model\BackorderProcessor;

/**
 * Class SendEmail
 *
 * Cron job class responsible for triggering the backorder email processing.
 * This class delegates the actual processing logic to the BackorderProcessor model.
 */
class SendEmail
{
    /**
     * @var BackorderProcessor
     *
     * Handles the backorder email processing logic.
     */
    protected $processor;

    /**
     * SendEmail constructor.
     *
     * @param BackorderProcessor $processor The processor instance used to handle backorder emails.
     */
    public function __construct(BackorderProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Execute the cron job.
     *
     * This method is called by Magento's cron scheduler and triggers
     * the backorder email processing through the BackorderProcessor.
     *
     * @return void
     */
    public function execute(): void
    {
    
        $this->processor->process();
        
    }
}