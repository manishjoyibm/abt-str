define(
    [
        'jquery',
        'ko',
        'mageUtils',
        'Avalara_AvaTax/js/model/address-model'
    ],
    function (
        $,
        ko,
        utils,
        addressModel
    )  {
    'use strict';

    return function (addressValidation) {
        return _.extend(addressValidation, {

            updateFormFields: function (form) {
                let field = 'street';
                $(form).find("input[name*=" + field + "]").each(function (index) {
                    let street;
                    if (index < addressModel.selectedAddress()[field].length) {
                        street = $(form).find("input[name*=" + field + "]").eq(index);
                    } else {
                        street = $(form).find("input[name*=" + field + "]").eq(index).attr('value', '');
                    }

                    if (street.val() !== addressModel.selectedAddress()[field][index]) {
                        $(street).val(addressModel.selectedAddress()[field][index]).trigger('change');
                    }
                });

                this.updateFieldValue(form, 'city');
                this.updateFieldValue(form, 'region');
                this.updateFieldValue(form, 'region_id');
                this.updateFieldValue(form, 'country_id');
                this.updateFieldValue(form, 'postcode');
            },

            updateFieldValue: function (form, field) {
                let fieldElement = $(form).find("input[name*=" + field + "]");
                if (['country_id', 'region_id'].indexOf(field) > -1) {
                    fieldElement = $(form).find("select[name*=" + field + "]");
                }
                if (fieldElement.val() !== addressModel.selectedAddress()[field]) {
                    $(fieldElement).val(addressModel.selectedAddress()[field]).trigger('change');
                }
            }
        });
    }
});
