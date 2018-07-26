define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'mage/translate'
    ],
    function (_, Component, creditCardData, cardNumberValidator, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Vindi_Payment/payment/vindi',
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardOwner: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardVerificationNumber: '',
                selectedCardType: null
            },
            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_type': this.selectedCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'cc_owner': this.creditCardOwner(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_cvv': this.creditCardVerificationNumber()
                    }
                };

                return data;
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardOwner',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'selectedCardType'
                    ]);
                return this;
            },

            initialize: function () {
                var self = this;
                this._super();

                //Set credit card number to credit card data object
                this.creditCardNumber.subscribe(function (value) {
                    var result;
                    self.selectedCardType(null);

                    if (value == '' || value == null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }
                    if (result.card !== null) {
                        self.selectedCardType(result.card.type);
                        creditCardData.creditCard = result.card;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        self.creditCardType(result.card.type);
                    }
                });

                this.creditCardOwner.subscribe(function (value) {
                    creditCardData.creditCardOwner = value;
                });

                //Set expiration year to credit card data object
                this.creditCardExpYear.subscribe(function (value) {
                    creditCardData.expirationYear = value;
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonth.subscribe(function (value) {
                    creditCardData.expirationYear = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumber.subscribe(function (value) {
                    creditCardData.cvvCode = value;
                });
            },

            isActive: function () {
                return true;
            },

            getCcAvailableTypes: function () {
                return window.checkoutConfig.payment.vindi_cc.availableTypes['vindi_cc'];
            },

            getCcMonths: function () {
                return window.checkoutConfig.payment.vindi_cc.months['vindi_cc'];
            },

            getCcYears: function () {
                return window.checkoutConfig.payment.vindi_cc.years['vindi_cc'];
            },

            hasVerification: function () {
                return window.checkoutConfig.payment.vindi_cc.hasVerification['vindi_cc'];
            },

            getCcAvailableTypesValues: function () {
                return _.map(this.getCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonthsValues: function () {
                return _.map(this.getCcMonths(), function (value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYearsValues: function () {
                return _.map(this.getCcYears(), function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            }
        });
    }
);
