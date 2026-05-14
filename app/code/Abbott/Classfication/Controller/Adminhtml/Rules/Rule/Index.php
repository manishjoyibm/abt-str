<?php

namespace Abbott\Classfication\Controller\Adminhtml\Rules\Rule;

class Index extends \Abbott\Classfication\Controller\Adminhtml\Rules\Rule
{
    const LABEL = 'Order Classfication';
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__(self::LABEL), __(self::LABEL));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__(self::LABEL));
        $this->_view->renderLayout('root');
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Abbott_Classfication::rules');
    }
}
