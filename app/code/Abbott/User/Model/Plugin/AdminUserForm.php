<?php

declare(strict_types=1);

namespace Abbott\User\Model\Plugin;

/**
 * Add the `esam_ticket_no` form field to the User main form.
 */
class AdminUserForm
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * UserForm constructor.
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
    ) {
        $this->coreRegistry = $registry;
    }

    /**
     * Add the `esam_ticket_no` field to the admin user edit form.
     *
     * @param \Magento\User\Block\User\Edit\Tab\Main $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Closure $proceed
    ) {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $subject->getForm();
        if (is_object($form)) {
            $model = $this->coreRegistry->registry('permissions_user');
            /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
            $fieldset = $form->getElement('base_fieldset');
            $userId = $model->getUserId();
            $requiredFieldClass = $userId ? '' : 'required-entry';
            $esamTicketValue = $model->hasData('esam_ticket_no') ? $model->getData('esam_ticket_no') : '';
            $isRequired = $userId ? false : true;
            $fieldset->addField(
                'esam_ticket_no',
                'text',
                [
                    'name' => 'esam_ticket_no',
                    'id' => 'esam_ticket_no',
                    'label' => __('ESAM Ticket No'),
                    'title' => __('ESAM Ticket No'),
                    'class' => $requiredFieldClass,
                    'required' => $isRequired,
                    'value' => $esamTicketValue,
                    'placeholder' => __('RITMxxxxx')
                ]
            );

            $subject->setForm($form);
        }

        return $proceed();
    }
}
