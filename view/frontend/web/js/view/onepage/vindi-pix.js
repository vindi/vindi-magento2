define(
    [
        'jquery',
        'uiComponent'
    ],
    function ($, Component) {
        'use strict';

        const elemVindiPixButtonCopy = '.vindi-pix-button-copy';

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
                    //@todo application condition frontend
                    jQuery('#copied-success').fadeIn(2000);
                    jQuery('#copied-success').fadeOut(3000);
                }, function() {
                    //@todo application condition frontend
                    jQuery('#copied-error').fadeIn(2000);
                    jQuery('#copied-error').fadeOut(3000);
                });
            }
        });
    }
);

