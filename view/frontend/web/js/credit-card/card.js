define(['vindi-card-mask'], function () {
    'use strict';

    return async function (ccName, ccNumber, ccExpDate, ccCvv, ccsingle, ccFront, ccBack) {

        const currentYear = parseInt(new Date().getFullYear().toString().substring(2, 4));
        const nextYear = currentYear + 1;

        let cardFront = '<svg version="1.1" id="vindi-cardfront" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 750 471" style="enable-background:new 0 0 750 471;" xml:space="preserve"> <g id="vindi-svg-front"> <g id="vindi-card-background"> <g id="vindi-page-1_1_"> <g id="vindi-amex_1_"> <path id="vindi-rectangle-1_1_" class="lightcolor grey" d="M40,0h670c22.1,0,40,17.9,40,40v391c0,22.1-17.9,40-40,40H40c-22.1,0-40-17.9-40-40V40 C0,17.9,17.9,0,40,0z" /> </g> </g> <path class="darkcolor greydark" d="M750,431V193.2c-217.6-57.5-556.4-13.5-750,24.9V431c0,22.1,17.9,40,40,40h670C732.1,471,750,453.1,750,431z" /> </g> <text transform="matrix(1 0 0 1 60.106 295.0121)" id="vindi-svgnumber" class="st2 st3 st4">0123 4567 8910 1112</text> <text transform="matrix(1 0 0 1 54.1064 428.1723)" id="vindi-svgname" class="st2 st5 st6">JOÃO DOS SANTOS</text> <text transform="matrix(1 0 0 1 54.1074 389.8793)" class="st7 st5 st8">nome do titular</text> <text transform="matrix(1 0 0 1 479.7754 388.8793)" class="st7 st5 st8">validade</text> <text transform="matrix(1 0 0 1 65.1054 241.5)" class="st7 st5 st8">número</text> <g> <text transform="matrix(1 0 0 1 574.4219 433.8095)" id="vindi-svgexpire" class="st2 st5 st9">12/23</text> <text transform="matrix(1 0 0 1 479.3848 417.0097)" class="st2 st10 st11">VÁLIDO</text> <text transform="matrix(1 0 0 1 479.3848 435.6762)" class="st2 st10 st11">ATÉ</text> <polygon class="st2" points="554.5,421 540.4,414.2 540.4,427.9 \t\t" /> </g> <g id="vindi-cchip"> <g> <path class="st2" d="M168.1,143.6H82.9c-10.2,0-18.5-8.3-18.5-18.5V74.9c0-10.2,8.3-18.5,18.5-18.5h85.3 c10.2,0,18.5,8.3,18.5,18.5v50.2C186.6,135.3,178.3,143.6,168.1,143.6z" /> </g> <g> <g> <rect x="82" y="70" class="st12" width="1.5" height="60" /> </g> <g> <rect x="167.4" y="70" class="st12" width="1.5" height="60" /> </g> <g> <path class="st12" d="M125.5,130.8c-10.2,0-18.5-8.3-18.5-18.5c0-4.6,1.7-8.9,4.7-12.3c-3-3.4-4.7-7.7-4.7-12.3 c0-10.2,8.3-18.5,18.5-18.5s18.5,8.3,18.5,18.5c0,4.6-1.7,8.9-4.7,12.3c3,3.4,4.7,7.7,4.7,12.3 C143.9,122.5,135.7,130.8,125.5,130.8z M125.5,70.8c-9.3,0-16.9,7.6-16.9,16.9c0,4.4,1.7,8.6,4.8,11.8l0.5,0.5l-0.5,0.5 c-3.1,3.2-4.8,7.4-4.8,11.8c0,9.3,7.6,16.9,16.9,16.9s16.9-7.6,16.9-16.9c0-4.4-1.7-8.6-4.8-11.8l-0.5-0.5l0.5-0.5 c3.1-3.2,4.8-7.4,4.8-11.8C142.4,78.4,134.8,70.8,125.5,70.8z" /> </g> <g> <rect x="82.8" y="82.1" class="st12" width="25.8" height="1.5" /> </g> <g> <rect x="82.8" y="117.9" class="st12" width="26.1" height="1.5" /> </g> <g> <rect x="142.4" y="82.1" class="st12" width="25.8" height="1.5" /> </g> <g> <rect x="142" y="117.9" class="st12" width="26.2" height="1.5" /> </g> </g> </g> </g> <g id="vindi-svg-back"> </g> </svg>';
        let cardBack = '<svg version="1.1" id="vindi-cardback" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 750 471" style="enable-background:new 0 0 750 471;" xml:space="preserve"> <g id="vindi-svg-back"> <line class="st0" x1="35.3" y1="10.4" x2="36.7" y2="11" /> </g> <g id="vindi-card-background"> <g id="vindi-page-1_2_"> <g id="vindi-amex_2_"> <path id="vindi-ectangle-1_2_" class="darkcolor greydark" d="M40,0h670c22.1,0,40,17.9,40,40v391c0,22.1-17.9,40-40,40H40c-22.1,0-40-17.9-40-40V40 C0,17.9,17.9,0,40,0z" /> </g> </g> <rect y="61.6" class="st2" width="750" height="78" /> <g> <path class="st3" d="M701.1,249.1H48.9c-3.3,0-6-2.7-6-6v-52.5c0-3.3,2.7-6,6-6h652.1c3.3,0,6,2.7,6,6v52.5 C707.1,246.4,704.4,249.1,701.1,249.1z" /> <rect x="42.9" y="198.6" class="st4" width="664.1" height="10.5" /> <rect x="42.9" y="224.5" class="st4" width="664.1" height="10.5" /> <path class="st5" d="M701.1,184.6H618h-8h-10v64.5h10h8h83.1c3.3,0,6-2.7,6-6v-52.5C707.1,187.3,704.4,184.6,701.1,184.6z" /> </g> <text transform="matrix(1 0 0 1 621.999 227.2734)" id="vindi-svgsecurity" class="st6 st7">985</text> <g class="st8"> <text transform="matrix(1 0 0 1 518.083 280.0879)" class="st9 st6 st10">cvv</text> </g> <rect x="58.1" y="378.6" class="st11" width="375.5" height="13.5" /> <rect x="58.1" y="405.6" class="st11" width="421.7" height="13.5" /> <text transform="matrix(1 0 0 1 59.5073 228.6099)" id="vindi-svgnameback" class="st12 st13">João da Silva</text> </g> </svg>';

        ccFront.insertAdjacentHTML("beforeend", cardFront);
        ccBack.innerHTML = cardBack;

        //Mask the Credit Card Number Input
        var ccNumberMask = new VindiVRMask(ccNumber, {
            mask: [
                {
                    mask: '000000000000000',
                    svgPattern: '0000 000000 00000',
                    regex: '^3[47]\\d{0,13}',
                    cardtype: 'american express'
                },
                {
                    mask: '0000000000000000',
                    svgPattern: '0000 0000 0000 0000',
                    regex: '^(5[1-5]\\d{0,2}|22[2-9]\\d{0,1}|2[3-7]\\d{0,2})\\d{0,12}',
                    cardtype: 'mastercard'
                },
                {
                    mask: '0000000000000000',
                    svgPattern: '0000 0000 0000 0000',
                    regex: '^(?:5[0678]\\d{0,2}|6304|67\\d{0,2})\\d{0,12}',
                    cardtype: 'maestro'
                },
                {
                    mask: '0000000000000000',
                    svgPattern: '0000 0000 0000 0000',
                    regex: '^4\\d{0,15}',
                    cardtype: 'visa'
                },
                {
                    mask: '0000000000000000',
                    svgPattern: '0000 0000 0000 0000',
                    cardtype: 'Unknown'
                }
            ],
            dispatch: function (appended, dynamicMasked) {
                var number = (dynamicMasked.value + appended).replace(/\D/g, '');

                for (var i = 0; i < dynamicMasked.compiledMasks.length; i++) {
                    let re = new RegExp(dynamicMasked.compiledMasks[i].regex);
                    if (number.match(re) != null) {
                        return dynamicMasked.compiledMasks[i];
                    }
                }
            }
        });

        //Mask the Expiration Date
        var ccExpDateMask = new VindiVRMask(ccExpDate, {
            mask: 'MM{/}YY',
            groups: {
                YY: new VindiVRMask.MaskedPattern.Group.Range([currentYear, 99]),
                MM: new VindiVRMask.MaskedPattern.Group.Range([1, 12]),
            }
        });

        //Mask the security code
        var ccCvvMask = new VindiVRMask(ccCvv, {
            mask: '0000',
        });

        let amex_single = `<svg id="vindi-layer1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="750" height="471" viewBox="0 0 750 471" enable-background="new 0 0 752 471" xml:space="preserve"><title>Slice 1</title><desc>Created with Sketch.</desc><g><g><path fill="#2557D6" d="M554.594,130.608l-14.521,35.039h29.121L554.594,130.608z M387.03,152.321c2.738-1.422,4.349-4.515,4.349-8.356c0-3.764-1.693-6.49-4.431-7.771c-2.492-1.42-6.328-1.584-10.006-1.584h-25.978v19.523h25.63C380.7,154.134,384.131,154.074,387.03,152.321z M54.142,130.608l-14.357,35.039h28.8L54.142,130.608z M722.565,355.08h-40.742v-18.852h40.578c4.023,0,6.84-0.525,8.537-2.177c1.471-1.358,2.494-3.336,2.494-5.733c0-2.562-1.023-4.596-2.578-5.813c-1.529-1.342-3.76-1.953-7.434-1.953c-19.81-0.67-44.523,0.609-44.523-27.211c0-12.75,8.131-26.172,30.27-26.172h42.025v-17.492h-39.045c-11.783,0-20.344,2.81-26.406,7.181v-7.181h-57.752c-9.233,0-20.074,2.279-25.201,7.181v-7.181H499.655v7.181c-8.207-5.898-22.057-7.181-28.447-7.181H403.18v7.181c-6.492-6.262-20.935-7.181-29.734-7.181h-76.134l-17.42,18.775l-16.318-18.775H149.847v122.675h111.586l17.95-19.076l16.91,19.076l68.78,0.059v-28.859h6.764c9.125,0.145,19.889-0.223,29.387-4.311v33.107h56.731v-31.976h2.736c3.492,0,3.838,0.146,3.838,3.621v28.348h172.344c10.941,0,22.38-2.786,28.712-7.853v7.853h54.668c11.375,0,22.485-1.588,30.938-5.653v-22.853C746.069,351.297,736.079,355.08,722.565,355.08z M372.734,326.113h-26.325v29.488h-41.006L279.425,326.5l-26.997,29.102h-83.569v-87.914h84.855l25.955,28.818l26.835-28.818h67.414c16.743,0,35.555,4.617,35.555,28.963C409.473,321.072,391.176,326.113,372.734,326.113z M499.323,322.127c2.98,4.291,3.41,8.297,3.496,16.047v17.428h-21.182v-10.998c0-5.289,0.512-13.121-3.41-17.209c-3.08-3.149-7.781-3.901-15.48-3.901h-22.545v32.108h-21.198v-87.914h48.706c10.685,0,18.462,0.472,25.386,4.148c6.658,4.006,10.848,9.494,10.848,19.523c-0.002,14.031-9.399,21.19-14.953,23.389C493.684,316.473,497.522,319.566,499.323,322.127z M586.473,285.869h-49.404v15.982h48.197v17.938h-48.197v17.492l49.404,0.078v18.242h-70.414v-87.914h70.414V285.869z M640.686,355.6h-41.09v-18.852h40.926c4.002,0,6.84-0.527,8.619-2.178c1.449-1.359,2.492-3.336,2.492-5.73c0-2.564-1.129-4.598-2.574-5.818c-1.615-1.34-3.842-1.948-7.514-1.948c-19.73-0.673-44.439,0.606-44.439-27.212c0-12.752,8.047-26.174,30.164-26.174h42.297v18.709h-38.703c-3.836,0-6.33,0.146-8.451,1.592c-2.313,1.423-3.17,3.535-3.17,6.322c0,3.316,1.963,5.574,4.615,6.549c2.228,0.771,4.617,0.996,8.211,0.996l11.359,0.308c11.449,0.274,19.313,2.25,24.092,7.069c4.105,4.232,6.311,9.578,6.311,18.625C673.829,346.771,661.963,355.6,640.686,355.6z M751.192,343.838L751.192,343.838L751.192,343.838L751.192,343.838z M477.061,287.287c-2.549-1.508-6.311-1.588-10.066-1.588h-25.979v19.744h25.631c4.104,0,7.594-0.144,10.414-1.812c2.734-1.646,4.371-4.678,4.371-8.438C481.432,291.434,479.795,288.711,477.061,287.287z M712.784,285.697c-3.838,0-6.389,0.145-8.537,1.588c-2.227,1.426-3.081,3.537-3.081,6.326c0,3.315,1.879,5.572,4.612,6.549c2.228,0.771,4.615,0.996,8.129,0.996l11.437,0.303c11.537,0.285,19.242,2.262,23.938,7.08c0.855,0.668,1.369,1.42,1.957,2.174v-25.014h-38.453L712.784,285.697L712.784,285.697z M373.47,285.697h-27.509v22.391h27.265c8.105,0,13.146-4.006,13.149-11.611C386.372,288.789,381.086,285.697,373.47,285.697z M189.872,285.697v15.984h46.315v17.938h-46.315v17.49h51.87l24.1-25.791l-23.076-25.621H189.872L189.872,285.697z M325.321,347.176v-70.482l-32.391,34.673L325.321,347.176z M191.649,206.025v15.148h176.263l-0.082-32.046h3.411c2.39,0.083,3.084,0.302,3.084,4.229v27.818h91.164v-7.461c7.353,3.924,18.789,7.461,33.838,7.461h38.353l8.209-19.522h18.197l8.026,19.522h73.906V202.63l11.189,18.543h59.227V98.59h-58.611v14.477l-8.207-14.477h-60.143v14.477l-7.537-14.477h-81.24c-13.6,0-25.551,1.89-35.207,7.158V98.59h-56.063v7.158c-6.146-5.43-14.519-7.158-23.826-7.158H180.784l-13.742,31.662L152.928,98.59H88.417v14.477L81.329,98.59H26.312L0.763,156.874v46.621l37.779-87.894h31.346l35.88,83.217v-83.217h34.435l27.61,59.625l25.365-59.625h35.126v87.894h-21.625l-0.079-68.837l-30.593,68.837h-18.524l-30.671-68.898v68.898H83.899l-8.106-19.605H31.865l-8.19,19.605H0.762v17.682h36.049l8.128-19.523h18.198l8.106,19.523h70.925V206.25l6.33,14.989h36.819L191.649,206.025z M469.401,125.849c6.818-7.015,17.5-10.25,32.039-10.25h20.424v18.833h-19.996c-7.696,0-12.047,1.14-16.233,5.208c-3.599,3.7-6.066,10.696-6.066,19.908c0,9.417,1.881,16.206,5.801,20.641c3.248,3.478,9.152,4.533,14.705,4.533h9.478l29.733-69.12h31.611l35.719,83.134v-83.133h32.123l37.086,61.213v-61.213h21.611v87.891h-29.898l-39.989-65.968v65.968h-42.968l-8.209-19.605h-43.827l-7.966,19.605h-24.688c-10.254,0-23.238-2.258-30.59-9.722c-7.416-7.462-11.271-17.571-11.271-33.553C458.026,147.182,460.329,135.266,469.401,125.849z M426.006,115.6h21.526v87.894h-21.526V115.6z M328.951,115.6h48.525c10.779,0,18.727,0.285,25.547,4.21c6.674,3.926,10.676,9.658,10.676,19.46c0,14.015-9.393,21.254-14.864,23.429c4.614,1.75,8.559,4.841,10.438,7.401c2.979,4.372,3.492,8.277,3.492,16.126v17.267h-21.279l-0.08-11.084c0-5.29,0.508-12.896-3.33-17.122c-3.082-3.09-7.782-3.763-15.379-3.763H350.05v31.97h-21.098L328.951,115.6L328.951,115.6z M243.902,115.6h70.479v18.303h-49.379v15.843h48.193v18.017h-48.193v17.553h49.379v18.177h-70.479V115.6L243.902,115.6z"/></g></g></svg>`;
        let visa_single = `<svg id="vindi-layer1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="750px" height="471px" viewBox="0 0 750 471" enable-background="new 0 0 750 471" xml:space="preserve"><title>Slice 1</title><desc>Created with Sketch.</desc><g id="visa" sketch:type="MSLayerGroup"><path id="Shape" sketch:type="MSShapeGroup" fill="#0E4595" d="M278.198,334.228l33.36-195.763h53.358l-33.384,195.763H278.198L278.198,334.228z"/><path id="path13" sketch:type="MSShapeGroup" fill="#0E4595" d="M524.307,142.687c-10.57-3.966-27.135-8.222-47.822-8.222c-52.725,0-89.863,26.551-90.18,64.604c-0.297,28.129,26.514,43.821,46.754,53.185c20.77,9.597,27.752,15.716,27.652,24.283c-0.133,13.123-16.586,19.116-31.924,19.116c-21.355,0-32.701-2.967-50.225-10.274l-6.877-3.112l-7.488,43.823c12.463,5.466,35.508,10.199,59.438,10.445c56.09,0,92.502-26.248,92.916-66.884c0.199-22.27-14.016-39.216-44.801-53.188c-18.65-9.056-30.072-15.099-29.951-24.269c0-8.137,9.668-16.838,30.559-16.838c17.447-0.271,30.088,3.534,39.936,7.5l4.781,2.259L524.307,142.687"/><path id="Path" sketch:type="MSShapeGroup" fill="#0E4595" d="M661.615,138.464h-41.23c-12.773,0-22.332,3.486-27.941,16.234l-79.244,179.402h56.031c0,0,9.16-24.121,11.232-29.418c6.123,0,60.555,0.084,68.336,0.084c1.596,6.854,6.492,29.334,6.492,29.334h49.512L661.615,138.464L661.615,138.464z M596.198,264.872c4.414-11.279,21.26-54.724,21.26-54.724c-0.314,0.521,4.381-11.334,7.074-18.684l3.607,16.878c0,0,10.217,46.729,12.352,56.527h-44.293V264.872L596.198,264.872z"/><path id="path16" sketch:type="MSShapeGroup" fill="#0E4595" d="M232.903,138.464L180.664,271.96l-5.565-27.129c-9.726-31.274-40.025-65.157-73.898-82.12l47.767,171.204l56.455-0.064l84.004-195.386L232.903,138.464"/><path id="path18" sketch:type="MSShapeGroup" fill="#F2AE14" d="M131.92,138.464H45.879l-0.682,4.073c66.939,16.204,111.232,55.363,129.618,102.415l-18.709-89.96C152.877,142.596,143.509,138.896,131.92,138.464"/></g></svg>`;
        let maestro_single = `<svg id="vindi-layer1" xmlns="http://www.w3.org/2000/svg" width="482.6" height="374.31" viewBox="0 0 482.6 374.31"> <title>maestro</title> <g> <path d="M278.8,421.77V397c0-9.35-6-15.64-15.55-15.72-5-.08-10.26,1.49-13.9,7-2.73-4.38-7-7-13.07-7a13.08,13.08,0,0,0-11.58,5.87v-4.88h-8.61v39.55h8.69V399.85c0-6.87,3.81-10.51,9.68-10.51,5.71,0,8.61,3.72,8.61,10.42v22h8.69V399.85c0-6.87,4-10.51,9.68-10.51,5.87,0,8.69,3.72,8.69,10.42v22ZM327.28,402V382.23h-8.61V387c-2.73-3.56-6.87-5.79-12.49-5.79-11.09,0-19.77,8.69-19.77,20.77s8.69,20.77,19.77,20.77c5.63,0,9.76-2.23,12.49-5.79v4.8h8.61Zm-32,0c0-6.95,4.55-12.66,12-12.66,7.12,0,11.91,5.46,11.91,12.66s-4.8,12.66-11.91,12.66C299.81,414.66,295.26,408.95,295.26,402ZM511.4,381.19a22.29,22.29,0,0,1,8.49,1.59,20.71,20.71,0,0,1,6.75,4.38,20,20,0,0,1,4.46,6.59,22,22,0,0,1,0,16.52,20,20,0,0,1-4.46,6.59,20.69,20.69,0,0,1-6.75,4.38,23.43,23.43,0,0,1-17,0,20.47,20.47,0,0,1-6.73-4.38,20.21,20.21,0,0,1-4.44-6.59,22,22,0,0,1,0-16.52,20.23,20.23,0,0,1,4.44-6.59,20.48,20.48,0,0,1,6.73-4.38A22.29,22.29,0,0,1,511.4,381.19Zm0,8.14a12.84,12.84,0,0,0-4.91.93,11.62,11.62,0,0,0-3.92,2.6,12.13,12.13,0,0,0-2.6,4,14.39,14.39,0,0,0,0,10.28,12.11,12.11,0,0,0,2.6,4,11.62,11.62,0,0,0,3.92,2.6,13.46,13.46,0,0,0,9.83,0,11.86,11.86,0,0,0,3.94-2.6,12,12,0,0,0,2.62-4,14.39,14.39,0,0,0,0-10.28,12,12,0,0,0-2.62-4,11.86,11.86,0,0,0-3.94-2.6A12.84,12.84,0,0,0,511.4,389.32ZM374.1,402c-.08-12.33-7.69-20.77-18.78-20.77-11.58,0-19.69,8.44-19.69,20.77,0,12.58,8.44,20.77,20.27,20.77,6,0,11.42-1.49,16.22-5.54l-4.22-6.37A18.84,18.84,0,0,1,356.4,415c-5.54,0-10.59-2.56-11.83-9.68h29.37C374,404.23,374.1,403.16,374.1,402Zm-29.45-3.47c.91-5.71,4.38-9.6,10.51-9.6,5.54,0,9.1,3.47,10,9.6Zm65.69-6.2A25.49,25.49,0,0,0,398,388.93c-4.72,0-7.53,1.74-7.53,4.63,0,2.65,3,3.39,6.7,3.89l4.05.58c8.61,1.24,13.82,4.88,13.82,11.83,0,7.53-6.62,12.91-18,12.91-6.45,0-12.41-1.66-17.13-5.13l4.05-6.7a21.07,21.07,0,0,0,13.16,4.14c5.87,0,9-1.74,9-4.8,0-2.23-2.23-3.47-6.95-4.14l-4.05-.58c-8.85-1.24-13.65-5.21-13.65-11.67,0-7.86,6.45-12.66,16.46-12.66,6.29,0,12,1.41,16.13,4.14Zm41.35-2.23H437.62V408c0,4,1.41,6.62,5.71,6.62a15.89,15.89,0,0,0,7.61-2.23l2.48,7.36a20.22,20.22,0,0,1-10.76,3.06c-10.18,0-13.73-5.46-13.73-14.65v-18h-8v-7.86h8v-12h8.69v12h14.06Zm29.78-8.85a18.38,18.38,0,0,1,6.12,1.08l-2.65,8.11a14,14,0,0,0-5.38-1c-5.63,0-8.44,3.64-8.44,10.18v22.17h-8.6V382.23H471V387a11.66,11.66,0,0,1,10.42-5.79Z" transform="translate(-132.9 -48.5)"/> <g id="_Group_" data-name="&lt;Group&gt;"> <rect x="176.05" y="31.89" width="130.5" height="234.51" fill="#7673c0"/> <path id="_Path_" data-name="&lt;Path&gt;" d="M317.24,197.64a148.88,148.88,0,0,1,57-117.26,149.14,149.14,0,1,0,0,234.51A148.88,148.88,0,0,1,317.24,197.64Z" transform="translate(-132.9 -48.5)" fill="#eb001b"/> <path d="M615.5,197.64A149.14,149.14,0,0,1,374.2,314.9a149.16,149.16,0,0,0,0-234.51A149.14,149.14,0,0,1,615.5,197.64Z" transform="translate(-132.9 -48.5)" fill="#00a1df"/> </g> </g></svg>`;
        let mastercard_single = `<svg id="vindi-layer1" xmlns="http://www.w3.org/2000/svg" width="482.51" height="374" viewBox="0 0 482.51 374"> <title>mastercard</title> <g> <path d="M220.13,421.67V396.82c0-9.53-5.8-15.74-15.32-15.74-5,0-10.35,1.66-14.08,7-2.9-4.56-7-7-13.25-7a14.07,14.07,0,0,0-12,5.8v-5h-7.87v39.76h7.87V398.89c0-7,4.14-10.35,9.94-10.35s9.11,3.73,9.11,10.35v22.78h7.87V398.89c0-7,4.14-10.35,9.94-10.35s9.11,3.73,9.11,10.35v22.78Zm129.22-39.35h-14.5v-12H327v12h-8.28v7H327V408c0,9.11,3.31,14.5,13.25,14.5A23.17,23.17,0,0,0,351,419.6l-2.49-7a13.63,13.63,0,0,1-7.46,2.07c-4.14,0-6.21-2.49-6.21-6.63V389h14.5v-6.63Zm73.72-1.24a12.39,12.39,0,0,0-10.77,5.8v-5h-7.87v39.76h7.87V399.31c0-6.63,3.31-10.77,8.7-10.77a24.24,24.24,0,0,1,5.38.83l2.49-7.46a28,28,0,0,0-5.8-.83Zm-111.41,4.14c-4.14-2.9-9.94-4.14-16.15-4.14-9.94,0-16.15,4.56-16.15,12.43,0,6.63,4.56,10.35,13.25,11.6l4.14.41c4.56.83,7.46,2.49,7.46,4.56,0,2.9-3.31,5-9.53,5a21.84,21.84,0,0,1-13.25-4.14l-4.14,6.21c5.8,4.14,12.84,5,17,5,11.6,0,17.81-5.38,17.81-12.84,0-7-5-10.35-13.67-11.6l-4.14-.41c-3.73-.41-7-1.66-7-4.14,0-2.9,3.31-5,7.87-5,5,0,9.94,2.07,12.43,3.31Zm120.11,16.57c0,12,7.87,20.71,20.71,20.71,5.8,0,9.94-1.24,14.08-4.56l-4.14-6.21a16.74,16.74,0,0,1-10.35,3.73c-7,0-12.43-5.38-12.43-13.25S445,389,452.07,389a16.74,16.74,0,0,1,10.35,3.73l4.14-6.21c-4.14-3.31-8.28-4.56-14.08-4.56-12.43-.83-20.71,7.87-20.71,19.88h0Zm-55.5-20.71c-11.6,0-19.47,8.28-19.47,20.71s8.28,20.71,20.29,20.71a25.33,25.33,0,0,0,16.15-5.38l-4.14-5.8a19.79,19.79,0,0,1-11.6,4.14c-5.38,0-11.18-3.31-12-10.35h29.41v-3.31c0-12.43-7.46-20.71-18.64-20.71h0Zm-.41,7.46c5.8,0,9.94,3.73,10.35,9.94H364.68c1.24-5.8,5-9.94,11.18-9.94ZM268.59,401.79V381.91h-7.87v5c-2.9-3.73-7-5.8-12.84-5.8-11.18,0-19.47,8.7-19.47,20.71s8.28,20.71,19.47,20.71c5.8,0,9.94-2.07,12.84-5.8v5h7.87V401.79Zm-31.89,0c0-7.46,4.56-13.25,12.43-13.25,7.46,0,12,5.8,12,13.25,0,7.87-5,13.25-12,13.25-7.87.41-12.43-5.8-12.43-13.25Zm306.08-20.71a12.39,12.39,0,0,0-10.77,5.8v-5h-7.87v39.76H532V399.31c0-6.63,3.31-10.77,8.7-10.77a24.24,24.24,0,0,1,5.38.83l2.49-7.46a28,28,0,0,0-5.8-.83Zm-30.65,20.71V381.91h-7.87v5c-2.9-3.73-7-5.8-12.84-5.8-11.18,0-19.47,8.7-19.47,20.71s8.28,20.71,19.47,20.71c5.8,0,9.94-2.07,12.84-5.8v5h7.87V401.79Zm-31.89,0c0-7.46,4.56-13.25,12.43-13.25,7.46,0,12,5.8,12,13.25,0,7.87-5,13.25-12,13.25-7.87.41-12.43-5.8-12.43-13.25Zm111.83,0V366.17h-7.87v20.71c-2.9-3.73-7-5.8-12.84-5.8-11.18,0-19.47,8.7-19.47,20.71s8.28,20.71,19.47,20.71c5.8,0,9.94-2.07,12.84-5.8v5h7.87V401.79Zm-31.89,0c0-7.46,4.56-13.25,12.43-13.25,7.46,0,12,5.8,12,13.25,0,7.87-5,13.25-12,13.25C564.73,415.46,560.17,409.25,560.17,401.79Z" transform="translate(-132.74 -48.5)"/> <g> <rect x="169.81" y="31.89" width="143.72" height="234.42" fill="#ff5f00"/> <path d="M317.05,197.6A149.5,149.5,0,0,1,373.79,80.39a149.1,149.1,0,1,0,0,234.42A149.5,149.5,0,0,1,317.05,197.6Z" transform="translate(-132.74 -48.5)" fill="#eb001b"/> <path d="M615.26,197.6a148.95,148.95,0,0,1-241,117.21,149.43,149.43,0,0,0,0-234.42,148.95,148.95,0,0,1,241,117.21Z" transform="translate(-132.74 -48.5)" fill="#f79e1b"/> </g> </g></svg>`;

        //pop in the appropriate card icon when detected
        ccNumberMask.on("accept", function () {
            switch (ccNumberMask.masked.currentMask.cardtype) {
                case 'american express':
                    ccsingle.innerHTML = amex_single;
                    swapColor('green');
                    break;
                case 'visa':
                    ccsingle.innerHTML = visa_single;
                    swapColor('lime');
                    break;
                case 'maestro':
                    ccsingle.innerHTML = maestro_single;
                    swapColor('yellow');
                    break;
                case 'mastercard':
                    ccsingle.innerHTML = mastercard_single;
                    swapColor('lightblue');

                    break;
                default:
                    ccsingle.innerHTML = '';
                    swapColor('grey');
                    break;
            }

        });


        // CREDIT CARD IMAGE JS
        document.querySelector('.vindi-preload').classList.remove('vindi-preload');
        document.getElementById('vindi-svgexpire').innerHTML = '01/' + nextYear;
        document.querySelector('.vindi-creditcard').addEventListener('click', function () {
            if (this.classList.contains('flipped')) {
                this.classList.remove('flipped');
            } else {
                this.classList.add('flipped');
            }
        })

        //On Input Change Events
        ccName.addEventListener('input', function () {
            if (ccName.value.length == 0) {
                document.getElementById('vindi-svgname').innerHTML = 'João da Silva';
                document.getElementById('vindi-svgnameback').innerHTML = 'João da Silva';
            } else {
                document.getElementById('vindi-svgname').innerHTML = this.value;
                document.getElementById('vindi-svgnameback').innerHTML = this.value;
            }
        });

        ccNumberMask.on('accept', function () {
            if (ccNumberMask.value.length == 0) {
                document.getElementById('vindi-svgnumber').innerHTML = '0123 4567 8910 1112';
            } else {
                let pattern = ccNumberMask.masked.currentMask.svgPattern;
                document.getElementById('vindi-svgnumber').innerHTML = applyPattern(ccNumberMask.value, pattern);
            }
        });

        ccExpDateMask.on('accept', function () {
            if (ccExpDateMask.value.length == 0) {
                document.getElementById('vindi-svgexpire').innerHTML = '01/' + nextYear;
            } else {
                document.getElementById('vindi-svgexpire').innerHTML = ccExpDateMask.value;
            }
        });

        ccCvvMask.on('accept', function () {
            if (ccCvvMask.value.length == 0) {
                document.getElementById('vindi-svgsecurity').innerHTML = '985';
            } else {
                document.getElementById('vindi-svgsecurity').innerHTML = ccCvvMask.value;
            }
        });

        //On Focus Events
        ccCvv.addEventListener('focus', function () {
            document.querySelector('.vindi-creditcard').classList.add('flipped');
        });

        ccCvv.addEventListener('blur', function () {
            document.querySelector('.vindi-creditcard').classList.remove('flipped');
        });

        //define the color swap function
        const swapColor = function (basecolor) {
            document.querySelectorAll('.lightcolor')
                .forEach(function (input) {
                    input.setAttribute('class', '');
                    input.setAttribute('class', 'lightcolor ' + basecolor);
                });
            document.querySelectorAll('.darkcolor')
                .forEach(function (input) {
                    input.setAttribute('class', '');
                    input.setAttribute('class', 'darkcolor ' + basecolor + 'dark');
                });
        };


        const applyPattern = function(inputNumber, pattern) {
            // Remove any non-digit characters from the input number
            const cleanedNumber = String(inputNumber).replace(/\D/g, '');

            // Initialize variables for the result and pattern index
            let result = '';
            let patternIndex = 0;

            // Iterate through each character in the pattern
            for (let i = 0; i < pattern.length; i++) {
                // Check if the current character is a placeholder (0) or other character
                if (pattern[i] === '0') {
                    // Check if there are still digits in the cleaned number
                    if (patternIndex < cleanedNumber.length) {
                        // Append the next digit from the cleaned number to the result
                        result += cleanedNumber[patternIndex];
                        patternIndex++;
                    } else {
                        // If no more digits in the cleaned number, break the loop
                        break;
                    }
                } else {
                    // If the current character is not a placeholder, append it to the result
                    result += pattern[i];
                }
            }

            return result;
        }

    }
});
