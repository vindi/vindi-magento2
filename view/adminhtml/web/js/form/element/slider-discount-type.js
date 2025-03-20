define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function ($, _, uiRegistry, Select) {
    'use strict';
    return Select.extend({
        initialize: function () {
            this._super();
            this.fieldDepend(this.value());
            return this;
        },

        onUpdate: function (value) {
            this.fieldDepend(value);
            return this._super();
        },

        fieldDepend: function (value) {
            var percentage = uiRegistry.get('index = percentage');
            var amount = uiRegistry.get('index = amount');
            var quantity = uiRegistry.get('index = quantity');

            if (value === 'percentage') {
                percentage.show();
                amount.hide();
                quantity.hide();
            } else if (value === 'amount') {
                percentage.hide();
                amount.show();
                quantity.hide();
            } else if (value === 'quantity') {
                percentage.hide();
                amount.hide();
                quantity.show();
            }
        }
    });
});
