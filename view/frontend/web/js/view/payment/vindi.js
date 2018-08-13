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
                component: 'Vindi_Payment/js/view/payment/method-renderer/vindi-method'
            }
        );
        return Component.extend({});
    }
);