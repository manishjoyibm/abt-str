<?php
namespace Abbott\ValidateAdminPassword\Block\Adminhtml\User\Edit\Tab;

use Magento\Framework\Data\Form\Element\Fieldset;

class Main extends \Magento\User\Block\User\Edit\Tab\Main
{
    /**
     * Add password input fields
     *
     * @param Fieldset $fieldset
     * @param string $passwordLabel
     * @param string $confirmationLabel
     * @param bool $isRequired
     * @return void
     */
    protected function _addPasswordFields(
        Fieldset $fieldset,
        $passwordLabel,
        $confirmationLabel,
        $isRequired = false
    ) {
        $requiredFieldClass = $isRequired ? ' required-entry' : '';
        $fieldset->addField(
            'password',
            'password',
            [
                'name' => 'password',
                'label' => $passwordLabel,
                'id' => 'customer_pass',
                'title' => $passwordLabel,
                'class' => 'input-text abbott-validate-admin-password' . $requiredFieldClass,
                'required' => $isRequired
            ]
        );
        $fieldset->addField(
            'confirmation',
            'password',
            [
                'name' => 'password_confirmation',
                'label' => $confirmationLabel,
                'id' => 'confirmation',
                'title' => $confirmationLabel,
                'class' => 'input-text abbott-validate-cpassword' . $requiredFieldClass,
                'required' => $isRequired
            ]
        );
    }
}
