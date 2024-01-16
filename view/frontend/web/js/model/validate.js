define([], function() {
        'use strict';

        return {
            isValidTaxvat(taxvat){
                if ((taxvat = taxvat.replace(/[^\d]/g,"")).length < 11)
                    return false

                if (taxvat.length === 11) {
                    return this.validateCpf(taxvat)
                }

                return this.validateCnpj(taxvat);
            },

            validateCpf: function (cpf) {
                if (cpf == "00000000000" ||
                    cpf == "11111111111" ||
                    cpf == "22222222222" ||
                    cpf == "33333333333" ||
                    cpf == "44444444444" ||
                    cpf == "55555555555" ||
                    cpf == "66666666666" ||
                    cpf == "77777777777" ||
                    cpf == "88888888888" ||
                    cpf == "99999999999" )
                    return false;

                var result;
                var sum = 0;

                for (var i=1; i<=9; i++)
                    sum = sum + parseInt(cpf[i-1]) * (11 - i);

                result = (sum * 10) % 11;

                if ((result == 10) || (result == 11))
                    result = 0;

                if (result != parseInt(cpf[9]))
                    return false;

                sum = 0;

                for (i = 1; i <= 10; i++)
                    sum = sum + parseInt(cpf[i-1]) * (12 - i);

                result = (sum * 10) % 11;

                if ((result == 10) || (result == 11))
                    result = 0;

                if (result != parseInt(cpf[10]))
                    return false;

                return true;
            },

            validateCnpj: function (cnpj) {
                if (/^(\d)\1+$/g.test(cnpj)) {
                    return false;
                }

                if (cnpj.length != 14) {
                    return false;
                }

                if (cnpj == "00000000000000" ||
                    cnpj == "11111111111111" ||
                    cnpj == "22222222222222" ||
                    cnpj == "33333333333333" ||
                    cnpj == "44444444444444" ||
                    cnpj == "55555555555555" ||
                    cnpj == "66666666666666" ||
                    cnpj == "77777777777777" ||
                    cnpj == "88888888888888" ||
                    cnpj == "99999999999999")
                    return false;

                let length = cnpj.length - 2
                let numbers = cnpj.substring(0,length);
                let digits = cnpj.substring(length);
                let sum = 0;
                let pos = length - 7;
                for (let i = length; i >= 1; i--) {
                    sum += numbers.charAt(length - i) * pos--;
                    if (pos < 2)
                        pos = 9;
                }
                let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
                if (result != digits.charAt(0)) {
                    return false;
                }

                length = length + 1;
                numbers = cnpj.substring(0,length);
                sum = 0;
                pos = length - 7;
                for (let i = length; i >= 1; i--) {
                    sum += numbers.charAt(length - i) * pos--;
                    if (pos < 2)
                        pos = 9;
                }

                result = sum % 11 < 2 ? 0 : 11 - sum % 11;
                if (result != digits.charAt(1))
                    return false;

                return true;
            }
        };
    }
);
