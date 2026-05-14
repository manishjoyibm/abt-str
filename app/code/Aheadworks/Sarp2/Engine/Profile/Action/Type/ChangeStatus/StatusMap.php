<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus;

use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class StatusMap
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus
 */
class StatusMap
{
    /**
     * @var array
     */
    private $map = [
        Status::ACTIVE => [Status::SUSPENDED, Status::CANCELLED, Status::PAUSE],
        Status::SUSPENDED => [Status::ACTIVE, Status::CANCELLED],
        Status::PENDING => [],
        Status::EXPIRED => [],
        Status::CANCELLED => [],
        Status::PAUSE => [Status::ACTIVE, Status::CANCELLED]
    ];

    /**
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        $this->map = array_merge($this->map, $map);
    }

    /**
     * Get allowed profile statuses
     *
     * @param string $status
     * @return array
     */
    public function getAllowedStatuses($status)
    {
        return isset($this->map[$status])
            ? $this->map[$status]
            : [];
    }
}
