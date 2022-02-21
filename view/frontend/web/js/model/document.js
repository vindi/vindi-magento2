define(
    [
        'jquery',
        'ko',
        'jQueryMask'
    ],

    function ($, ko) {
        'use strict';

        $('input[name="payment[_document]"]').mask('999.999.999-99');

        const value = ko.observable('');

        return {
            value: value
        };
    }
);
