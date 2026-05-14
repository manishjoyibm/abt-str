<?php

declare(strict_types=1);

namespace Abbott\Mfa\Model\Config\Source;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class UserRoles implements ArrayInterface
{
    /**
     * CollectionFactory Object
     *
     * @var CollectionFactory
     */
    protected CollectionFactory $roleCollectionFactory;

    /**
     * Construct
     *
     * @param CollectionFactory $roleCollectionFactory
     */
    public function __construct(
        CollectionFactory $roleCollectionFactory
    ) {
        $this->roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $roles = $this->roleCollectionFactory->create()->addFieldToFilter(
            'role_type',
            ['eq' => 'G']
        );
        $res = [];
        foreach ($roles as $role) {
            $res[] = [
                'value' => $role->getRoleId(),
                'label' => $role->getRoleName(),
            ];
        }

        return $res;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $options = $this->toOptionArray();
        $return = [];

        foreach ($options as $option) {
            $return[$option['value']] = $option['label'];
        }

        return $return;
    }
}
