<?php

namespace Abbott\GlobalOptOut\Model;

use Magento\Framework\ObjectManagerInterface;

class GlobaloptFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create function
     *
     * @param array $arguments
     * @return Globalopt|mixed
     */
    public function create(array $arguments = []): mixed
    {
        return $this->objectManager->create(
            Globalopt::class,
            $arguments
        );
    }
}
