<?php

namespace Abbott\Classfication\Controller\Adminhtml\Rules\Rule;

class NewAction extends \Abbott\Classfication\Controller\Adminhtml\Rules\Rule
{
    /**
     * New action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
