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

            /** @inheritdoc */
            initialize: function () {
                this._super();
            },

            copyQrCodeKey: function () {
                const value = this?.qrCodeKey;

                navigator.clipboard.writeText(value).then(function() {
                    jQuery('#copied-success').fadeIn(2000);
                    jQuery('#copied-success').fadeOut(3000);
                }, function() {
                    jQuery('#copied-error').fadeIn(2000);
                    jQuery('#copied-error').fadeOut(3000);
                });
            }
        });
    }
);

