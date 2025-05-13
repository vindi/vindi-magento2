define([
    'jquery',
    'mage/translate',
    'underscore',
    'mage/url',
    'Magento_Ui/js/model/messageList'
], function ($, $t, _, url, messageList) {
    'use strict';

    return function () {
        let cardSelector = $("#card-selector");
        let productId = $("#product-id").text();
        let submitButton = $("#payment-oneclickbuy");

        submitButton.on("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            let param = {
                profile: cardSelector.val(),
                productId: productId
            };

            $.ajax({
                showLoader: true,
                url: BASE_URL + 'vindi_vr/oneclickbuy/transaction',
                data: param,
                type: "POST",
                dataType: 'json'
            }).done(function (response) {
                if (response.success) {
                    location.href = BASE_URL + 'checkout/onepage/success';
                } else {
                    messageList.addErrorMessage({ message: response.message || $t('Não foi possível concluir a compra. Tente novamente.') });
                }
            }).fail(function () {
                messageList.addErrorMessage({ message: $t('Erro de comunicação com o servidor. Tente novamente.') });
            });
        });

        return $.mage;
    };
});
