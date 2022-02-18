define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'mage/translate',
        'jquery',
        'mageUtils',
        'Vindi_Payment/js/model/document',
        'Vindi_Payment/js/model/validate'
    ],

    function (_, Component, $t, $, utils, document, documentValidate) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Vindi_Payment/payment/vindi-pix',
                document: document
            },

            getInfoMessage: function () {
                return window?.checkoutConfig?.payment?.vindi_pix?.info_message;
            },

            isActiveDocument: function () {
                return window?.checkoutConfig?.payment?.vindi_pix?.enabledDocument;
            },

            checkCpf: function () {
                const message = documentValidate.isValidCpf(this?.document?.value()) ? '' : 'CPF inválido';
                $('#cpfResponse').text(message);
            },


            validate: function () {
                const self = this;
                const documentValue = this?.document?.value();

                if (!documentValue || documentValue === '') {
                    self.messageContainer.addErrorMessage({'message': ('CPF é obrigatório')});
                    return false;
                }

                if (!documentValidate.isValidCpf(documentValue)) {
                    self.messageContainer.addErrorMessage({'message': ('CPF não é válido')});
                    return false;
                }

                return true;
            },

            getData: function() {
                return {
                    'method': this?.item?.method,
                    'additional_data': {
                        'document': this?.document?.value()
                    }
                };
            },

        });
    }
);

