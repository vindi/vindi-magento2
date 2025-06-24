require([
    'jquery',
    'mage/translate',
    'underscore',
    'mage/url',
    'Magento_Ui/js/model/messageList'
], function ($, $t, _, url, messageList) {
    'use strict';

    $(document).ready(function () {
        let cardSelector = $("#card-selector");
        let productId = $("#product-id").text();
        let submitButton = $("#payment-oneclickbuy");

        submitButton.off("click").on("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (submitButton.data('submitted')) {
                return;
            }

            submitButton.data('submitted', true);
            submitButton.prop('disabled', true);

            let param = {
                profile: cardSelector.val(),
                productId: productId
            };

            $.ajax({
                showLoader: true,
                url: url.build('vindi_vr/oneclickbuy/transaction'),
                data: param,
                type: "POST",
                dataType: 'json'
            }).done(function (response) {
                if (response.success) {
                    location.href = response.redirect_url;
                } else {
                    messageList.addErrorMessage({
                        message: response.message || $t('Não foi possível concluir a compra. Tente novamente.')
                    });
                }
            }).fail(function () {
                messageList.addErrorMessage({
                    message: $t('Erro de comunicação com o servidor. Tente novamente.')
                });
            }).always(function () {
                submitButton.data('submitted', false);
                submitButton.prop('disabled', false);
            });
        });
    });
});
