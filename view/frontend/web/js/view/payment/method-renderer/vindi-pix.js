define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'mage/translate',
        'jquery',
        'mageUtils'
    ],

    function (_, Component, $t, $, utils) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Vindi_Payment/payment/vindi-pix'
            },

            getInfoMessage: function () {
                return window?.checkoutConfig?.payment?.vindi_pix?.info_message;
            }
        });
    }
);

