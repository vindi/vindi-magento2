define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function ($, _, uiRegistry, select) {
    'use strict';
    return select.extend({
        initialize: function () {
            this._super();
            var status = this.value();
            this.fieldDepend(status);
            this.setupFieldListeners();
            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.fieldDepend(value);
            return this._super();
        },

        /**
         * Update field dependency
         *
         * @param {String} value
         */
        fieldDepend: function (value) {
            let billing_cycles = uiRegistry.get('index = billing_cycles');
            let billing_trigger_day = uiRegistry.get('index = billing_trigger_day');
            let billing_trigger_day_type_on_period = uiRegistry.get('index = billing_trigger_day_type_on_period');
            let billing_trigger_day_based_on_period = uiRegistry.get('index = billing_trigger_day_based_on_period');

            if (value === "defined") {
                billing_cycles.show();
                billing_trigger_day.hide();
                billing_trigger_day_type_on_period.show();
                billing_trigger_day_based_on_period.show();
            } else {
                billing_cycles.hide();
                billing_trigger_day.show();
                billing_trigger_day_type_on_period.hide();
                billing_trigger_day_based_on_period.hide();
            }
        },

        /**
         * Sets up listeners for related fields.
         */
        setupFieldListeners: function () {
            this.setupBillingTriggerTypeListener();
            this.setupDurationListener();
        },

        /**
         * Sets up listener for changes to billing_trigger_type field.
         */
        setupBillingTriggerTypeListener: function () {
            var self = this;
            uiRegistry.get('index = billing_trigger_type', function (billingTriggerType) {
                billingTriggerType.on('value', function (value) {
                    self.updateDurationBasedOnBillingTriggerType(value);
                });
            });
        },

        /**
         * Sets up listener for changes to duration field.
         */
        setupDurationListener: function () {
            var self = this;
            uiRegistry.get('index = duration', function (duration) {
                duration.on('value', function (value) {
                    self.updateBillingTriggerTypeBasedOnDuration(value);
                });
            });
        },

        /**
         * Updates the duration field based on the billing trigger type value.
         *
         * @param {String} value
         */
        updateDurationBasedOnBillingTriggerType: function (value) {
            var durationField = uiRegistry.get('index = duration');
            if (durationField) {
                if (value === 'day_of_month') {
                    durationField.value('undefined');
                } else if (value === 'based_on_period') {
                    durationField.value('defined');
                }
            }
        },

        /**
         * Updates the billing_trigger_type field based on the duration value.
         *
         * @param {String} value
         */
        updateBillingTriggerTypeBasedOnDuration: function (value) {
            var billingTriggerTypeField = uiRegistry.get('index = billing_trigger_type');
            if (billingTriggerTypeField) {
                if (value === 'undefined') {
                    billingTriggerTypeField.value('day_of_month');
                } else if (value === 'defined') {
                    billingTriggerTypeField.value('based_on_period');
                }
            }
        }
    });
});
