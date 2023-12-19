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

            validateCpf: function (c) {
                if (c == "00000000000" ||
                    c == "11111111111" ||
                    c == "22222222222" ||
                    c == "33333333333" ||
                    c == "44444444444" ||
                    c == "55555555555" ||
                    c == "66666666666" ||
                    c == "77777777777" ||
                    c == "88888888888" ||
                    c == "99999999999" )
                    return false;

                var r;
                var s = 0;

                for (var i=1; i<=9; i++)
                    s = s + parseInt(c[i-1]) * (11 - i);

                r = (s * 10) % 11;

                if ((r == 10) || (r == 11))
                    r = 0;

                if (r != parseInt(c[9]))
                    return false;

                s = 0;

                for (i = 1; i <= 10; i++)
                    s = s + parseInt(c[i-1]) * (12 - i);

                r = (s * 10) % 11;

                if ((r == 10) || (r == 11))
                    r = 0;

                if (r != parseInt(c[10]))
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

                let tamanho = cnpj.length - 2
                let numeros = cnpj.substring(0,tamanho);
                let digitos = cnpj.substring(tamanho);
                let soma = 0;
                let pos = tamanho - 7;
                for (let i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2)
                        pos = 9;
                }
                let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(0)) {
                    return false;
                }

                tamanho = tamanho + 1;
                numeros = cnpj.substring(0,tamanho);
                soma = 0;
                pos = tamanho - 7;
                for (let i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2)
                        pos = 9;
                }

                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(1))
                    return false;

                return true;
            }
        };
    }
);
