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

        var types = [
            {
                title: 'Visa',
                type: 'visa',
                pattern: '^4\\d*$',
                gaps: [4, 8, 12],
                lengths: [16],
                code: {
                    name: 'CVV',
                    size: 3
                }
            },
            {
                title: 'MasterCard',
                type: 'mastercard',
                pattern: '^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$',
                gaps: [4, 8, 12],
                lengths: [16],
                code: {
                    name: 'CVC',
                    size: 3
                }
            },
            {
                title: 'American Express',
                type: 'american_express',
                pattern: '^3([47]\\d*)?$',
                isAmex: true,
                gaps: [4, 10],
                lengths: [15],
                code: {
                    name: 'CID',
                    size: 4
                }
            },
            {
                title: 'Diners',
                type: 'diners_club',
                pattern: '^(3(0[0-5]|095|6|[8-9]))\\d*$',
                gaps: [4, 10],
                lengths: [14, 16, 17, 18, 19],
                code: {
                    name: 'CVV',
                    size: 3
                }
            },
            {
                title: 'Hipercard',
                type: 'hipercard',
                pattern: '^(?:3841[046]0|6(?:06282|37(?:095|5(?:68|99)|6(?:09|12))))',
                gaps: [4, 8, 12],
                lengths: [16],
                code: {
                    name: 'CVC',
                    size: 3
                }
            },
            {
                title: 'Elo',
                type: 'elo',
                pattern: '^(4(0117[89]|3(1274|8935)|5(1416|7(393|63[12])))|50(4175|6(699|7([0-6]\\d|7[0-8]))|9\\d{3})|6(27780|36(297|368)|5(0(0(3[1-35-9]|4\\d|5[01])|4(0[5-9]|([1-3]\\d|8[5-9]|9\\d))|5([0-2]\\d|3[0-8]|4[1-9]|[5-8]\\d|9[0-8])|7(0\\d|1[0-8]|2[0-7])|9(0[1-9]|[1-6]\\d|7[0-8]))|16(5[2-9]|[67]\\d)|50([01]\\d|2[1-9]|[34]\\d|5[0-8]))))',
                gaps: [4, 8, 12],
                lengths: [16],
                code: {
                    name: 'CVC',
                    size: 3
                }
            }
        ];

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
            validate: function(){
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
                    var card = self.getCardTypes(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }
                    if (card) {
                        self.selectedCardType(card.type);
                        creditCardData.creditCard = card;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        self.creditCardType(card.type);
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
            getCardTypes: function (cardNumber) {
                var i, value,
                    result = [];

                if (utils.isEmpty(cardNumber)) {
                    return result;
                }

                if (cardNumber === '') {
                    return $.extend(true, {}, types);
                }

                for (i = 0; i < types.length; i++) {
                    value = types[i];

                    if (new RegExp(value.pattern).test(cardNumber)) {
                        return $.extend(true, {}, value);
                    }
                }

                return result;
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
