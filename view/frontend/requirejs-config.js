const config = {
    paths: {
        'vindi-card-form': 'Vindi_Payment/js/credit-card/card',
        'vindi-card-mask': 'Vindi_Payment/js/credit-card/mask',
        'jQueryMask': 'Vindi_Payment/js/libs/jquery.mask.min',
        'mage/url': 'mage/url'
    },
    shim: {
        'vindi-card-mask': {}
    },
    map: {
        "*": {
            "vindi_vr/oneclickbuy": "Vindi_Payment/js/product/oneclickbuy"
        }
    }
};
