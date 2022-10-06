define(
    [
        'jquery',
        'Vindi_Payment/js/view/onepage/model/vindi-pix-copy-paste'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Vindi_Payment/onepage/vindi-pix'
            },
        });
    }
);

