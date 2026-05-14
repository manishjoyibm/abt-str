<?php

declare(strict_types=1);

namespace Abbott\OneTrust\Api;

interface NewsletterSubscriptionInterface
{
    /**
     * POST for newsletter api
     * @return string
     */
    public function updateNewsletter(): string;
}
