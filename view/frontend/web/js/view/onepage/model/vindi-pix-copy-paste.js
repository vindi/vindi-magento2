define(
    [
        'jquery',
        'uiComponent'
    ],
    function ($, Component) {
        'use strict';

        const elemVindiPixButtonCopy = '.vindi-pix-button-copy';

        return Component.extend({

            copyQrCodeKey: function () {
                const value = this?.qrCodeKey;

                navigator.clipboard.writeText(value).then(function() {
                    //@todo application condition frontend
                    console.log("success", value);
                }, function() {
                    //@todo application condition frontend
                    console.log("fail");
                });
            }
        });
    }
);
