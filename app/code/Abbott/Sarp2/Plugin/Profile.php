<?php
namespace Abbott\Sarp2\Plugin;

class Profile {
    public function afterGetCustomerFullname(
    \Aheadworks\Sarp2\Model\Profile $subject,$result
    ) {
        $result = trim($result);
        return $result;
    }
}
