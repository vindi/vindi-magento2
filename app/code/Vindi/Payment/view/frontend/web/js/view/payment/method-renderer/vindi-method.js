define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'mage/translate',
        'jquery',
        'mageUtils'
    ],
    function (_, Component, creditCardData, cardNumberValidator, $t, $, utils) {
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
                selectedCardType: null,
                selectedInstallments: null
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
                        'cc_cvv': this.creditCardVerificationNumber(),
                        'cc_installments': this.selectedInstallments(),
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
                        'selectedCardType',
                        'selectedInstallments'
                    ]);
                return this;
            },
            validate: function () {
                if (!this.selectedCardType() || this.selectedCardType() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Type.')});
                    return false;
                }
                if (!this.creditCardExpYear() || this.creditCardExpYear() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Expiry Year.')});
                    return false;
                }
                if (!this.creditCardExpMonth() || this.creditCardExpMonth() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Expiry Month.')});
                    return false;
                }
                if (!this.creditCardNumber() || this.creditCardNumber() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Number.')});
                    return false;
                }
                if (!this.creditCardOwner() || this.creditCardOwner() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Owner Name.')});
                    return false;
                }
                if (!this.creditCardVerificationNumber() || this.creditCardVerificationNumber() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card CVV.')});
                    return false;
                }
                if (!this.selectedInstallments() || this.selectedInstallments() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the number of Installments.')});
                    return false;
                }

                return true;
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

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
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
                this.selectedInstallments.subscribe(function (value) {
                    creditCardData.selectedInstallments = value;
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
            getCcInstallments: function () {
                return window.checkoutConfig.payment.vindi_cc.installments['vindi_cc'];
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
            },
            getCcTypesValues: function () {
                return _.map(this.getCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'name': value
                    }
                });
            },
            getCcInstallmentsAvailable: function () {
                return _.map(this.getCcInstallments(), function (value, key) {
                    return {
                        'value': key,
                        'text': value
                    }
                });
            },
        });
    }
);
