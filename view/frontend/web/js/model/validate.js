define([], function() {
        'use strict';

        return {
            isValidCpf(c){
                if((c = c.replace(/[^\d]/g,"")).length != 11)
                    return false

                if (c == "00000000000" ||
                    c == "11111111111" ||
                    c == "22222222222" ||
                    c == "33333333333" ||
                    c == "44444444444" ||
                    c == "55555555555" ||
                    c == "66666666666" ||
                    c == "77777777777" ||
                    c == "88888888888" ||
                    c == "99999999999" ||
                    c == "12312312387")
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
            }
        };
    }
);
