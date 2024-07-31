define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'mage/translate',
        'jquery',
        'mageUtils',
        'Vindi_Payment/js/model/taxvat',
        'Vindi_Payment/js/model/validate'
    ],

    function (_, Component, $t, $, utils, taxvat, documentValidate) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Vindi_Payment/payment/vindi-bankslippix',
                taxvat: taxvat
            },

            initialize: function () {
                this._super();
                this.taxvat.value(window?.checkoutConfig?.payment?.vindi_bankslippix?.customer_taxvat);
            },

            getInfoMessage: function () {
                return window?.checkoutConfig?.payment?.vindi_bankslippix?.info_message;
            },

            isActiveDocument: function () {
                return window?.checkoutConfig?.payment?.vindi_bankslippix?.enabledDocument;
            },

            checkCpf: function (self, event) {
                this.formatTaxvat(event.target)
                const message = documentValidate.isValidTaxvat(this?.taxvat?.value()) ? '' : 'CPF/CNPJ inválido';
                $('#cpfResponseBankSlipPix').text(message);
            },

            formatTaxvat: function (target) {
                taxvat.formatDocument(target)
            },

            validate: function () {
                const self = this;
                const documentValue = this?.taxvat?.value();

                if (!this.isActiveDocument()) return true;

                if (!documentValue || documentValue === '') {
                    self.messageContainer.addErrorMessage({'message': ('CPF/CNPJ é obrigatório')});
                    return false;
                }

                if (!documentValidate.isValidTaxvat(documentValue)) {
                    self.messageContainer.addErrorMessage({'message': ('CPF/CNPJ não é válido')});
                    return false;
                }

                return true;
            },

            getData: function() {
                return {
                    'method': this?.item?.method,
                    'additional_data': {
                        'document': this?.taxvat?.value()
                    }
                };
            },

        });
    }
);
