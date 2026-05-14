<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Setup\Patch\Data;

use Amasty\Base\Model\MagentoVersion;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SaveCustomerIdForSubscriber implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var string
     */
    private $subscriberTable;

    public function __construct(
        ResourceConnection $resourceConnection,
        MagentoVersion $magentoVersion
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->magentoVersion = $magentoVersion;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        if (version_compare($this->magentoVersion->get(), '2.4.0', '<')) {
            return $this;
        }
        $this->subscriberTable = $this->resourceConnection->getTableName('newsletter_subscriber');
        foreach ($this->getGuestSubscribers() as $subscriber) {
            $this->updateSubscriber($subscriber['email'], (int)$subscriber['entity_id']);
        }

        return $this;
    }

    /**
     * @return array format: [id => ['email' => [string], 'entity_id' => [string]]]
     */
    private function getGuestSubscribers(): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(['main_table' => $this->subscriberTable], [])
            ->joinInner(
                ['customer' => $this->resourceConnection->getTableName('customer_entity')],
                'customer.email = main_table.subscriber_email',
                ['customer.email', 'customer.entity_id']
            )
            ->where('main_table.customer_id = 0');

        return $connection->fetchAll($select);
    }

    private function updateSubscriber(string $customerEmail, int $customerId): void
    {
        $this->resourceConnection->getConnection()->update(
            $this->subscriberTable,
            ['customer_id' => $customerId],
            ['subscriber_email = ?' => $customerEmail]
        );
    }
}
