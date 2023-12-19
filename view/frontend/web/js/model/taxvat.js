define(
    [
        'jquery',
        'ko'
    ],

    function ($, ko) {
        'use strict';

        $('input[name="payment[_document]"]').mask('999.999.999-99');

        const value = ko.observable('');

        return {
            value: value,

            formatDocument: function(input) {
                let value = input.value.replace(/\D/g, '');
                let isCpf = value.length <= 11;

                input.value = isCpf
                    ? value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
                    : value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
            }
        };
    }
);
