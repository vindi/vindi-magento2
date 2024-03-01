document.addEventListener("DOMContentLoaded", function () {
    const mask = {
        cartao(value, cardType) {
            if (cardType === 'amex') {
                return value
                    .replace(/\D/g, '')
                    .replace(/^(\d{4})(\d{6})(\d{5}).*/, '$1-$2-$3');
            } else {
                return value
                    .replace(/\D/g, '')
                    .replace(/(\d{4})(?=\d)/g, '$1-')
                    .substr(0, 19);
            }
        },
        expiracao(value) {
            return value
                .replace(/\D/g, '')
                .replace(/(\d{2})(\d)/, '$1/$2')
                .replace(/(\/\d{2})\d+?$/, '$1');
        }
    };

    const cardNumberInput     = document.getElementById('cc_number');
    const cardExpirationInput = document.getElementById('cc_exp_date');
    const cardTypeInputs      = document.querySelectorAll('.card-type-input');
    const form                = document.getElementById('payment-profile-form');

    cardTypeInputs.forEach(input => {
        input.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });

    cardNumberInput.addEventListener('input', function (e) {
        e.target.value = mask.cartao(e.target.value);
    });

    cardNumberInput.addEventListener('input', function (e) {
        const value = e.target.value.replace(/\D/g, '');
        let cardType = '';

        const visaPattern = /^4/;
        const mastercardPattern = /^(5[1-5]|2[2-7])/;
        const amexPattern = /^3[47]/;

        if (visaPattern.test(value)) {
            document.getElementById('visa').checked = true;
            cardType = 'visa';
        } else if (mastercardPattern.test(value)) {
            document.getElementById('mastercard').checked = true;
            cardType = 'mastercard';
        } else if (amexPattern.test(value)) {
            document.getElementById('amex').checked = true;
            cardType = 'amex';
        }

        e.target.value = mask.cartao(value, cardType);
    });

    cardExpirationInput.addEventListener('input', function (e) {
        e.target.value = mask.expiracao(e.target.value);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const cardNumber       = document.getElementById('cc_number').value.replace(/-/g, '');
        const cardExpDate      = document.getElementById('cc_exp_date').value;
        const cardCVV          = document.getElementById('cc_cvv').value;
        const cardTypeSelected = document.querySelector('input[name="cc_type"]:checked');

        if (!cardTypeSelected) {
            alert($.mage.__('Card number is invalid.'));
            return false;
        }

        if (!isValidCardNumber(cardNumber)) {
            alert($.mage.__('Card number is invalid.'));
            return false;
        }

        if (!isValidExpiryDate(cardExpDate)) {
            alert($.mage.__('Expiration date is invalid.'));
            return false;
        }

        if (!isValidCVV(cardCVV, cardTypeSelected.value)) {
            alert($.mage.__('CVV is invalid.'));
            return false;
        }

        form.submit();
    });

    function isValidCardNumber(number) {
        let sum = 0;
        let shouldDouble = false;
        for (let i = number.length - 1; i >= 0; i--) {
            let digit = parseInt(number.charAt(i));

            if (shouldDouble) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }

            sum += digit;
            shouldDouble = !shouldDouble;
        }

        return sum % 10 === 0;
    }

    function isValidExpiryDate(date) {
        const [month, year] = date.split('/');
        const expiryDate = new Date(`20${year}`, month - 1);
        const currentDate = new Date();

        return expiryDate > currentDate;
    }

    function isValidCVV(cvv, cardType) {
        const cvvLength = cvv.length;
        return (cardType === 'amex' && cvvLength === 4) || (['visa', 'mastercard'].includes(cardType) && cvvLength === 3);
    }
});
