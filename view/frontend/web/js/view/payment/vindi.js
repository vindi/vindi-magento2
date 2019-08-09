define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'vindi_creditcard',
                component: 'Vindi_Payment/js/view/payment/method-renderer/vindi-creditcard'
            },
            {
                type: 'vindi_bankslip',
                component: 'Vindi_Payment/js/view/payment/method-renderer/vindi-bankslip'
            }
        );
        return Component.extend({});
    }
);