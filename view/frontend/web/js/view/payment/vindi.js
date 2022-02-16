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
                type: 'vindi',
                component: 'Vindi_Payment/js/view/payment/method-renderer/vindi-cc'
            },
            {
                type: 'vindi_bankslip',
                component: 'Vindi_Payment/js/view/payment/method-renderer/vindi-bankslip'
            },
            {
                type: 'vindi_pix',
                component: 'Vindi_Payment/js/view/payment/method-renderer/vindi-pix'
            }
        );
        return Component.extend({});
    }
);
