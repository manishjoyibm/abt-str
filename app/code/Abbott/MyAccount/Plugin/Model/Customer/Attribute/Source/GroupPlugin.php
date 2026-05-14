<?php

namespace Abbott\MyAccount\Plugin\Model\Customer\Attribute\Source;

use Magento\Customer\Model\Customer\Attribute\Source\Group;

class GroupPlugin
{
    public const GRP_CODE = 'Consumer';
    public const LABEL = 'label';

    /**
     * AfterGetAllOptions function
     *
     * @param Group $subject
     * @param array $details
     * @return array
     */
    public function afterGetAllOptions(Group $subject, $details)
    {
        $consumerKey = array_search(self::GRP_CODE, array_column($details, self::LABEL));
        if ($consumerKey) {
            array_unshift($details, $details[$consumerKey]);
            array_splice($details, $consumerKey + 1, 1);
        }
        return $details;
    }
}
