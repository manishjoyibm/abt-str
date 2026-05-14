<?php

namespace Abbott\Classfication\Controller\Adminhtml\Rules\Rule;

class Edit extends \Abbott\Classfication\Controller\Adminhtml\Rules\Rule
{
    private const NEW_RULE = "New Rule";
    private const EDIT_RULE = "Edit Rule";

    /**
     * Rule edit action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Abbott\Classfication\Model\Rule $model */
        $model = $this->ruleFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('abbott_classfication/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $this->coreRegistry->register('current_rule', $model);

        $this->_initAction();

        $this->_view->getLayout()
                    ->getBlock('rules_rule_edit')
                    ->setData('action', $this->getUrl('abbott_classfication/*/save'));

        $this->_addBreadcrumb(
            $id ? __(self::EDIT_RULE) : __(self::NEW_RULE),
            $id ? __(self::EDIT_RULE) : __(self::NEW_RULE)
        );

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? $model->getName() : __(self::NEW_RULE)
        );
        $this->_view->renderLayout();
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
