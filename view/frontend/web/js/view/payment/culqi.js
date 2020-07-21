define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'culqi',
                component: 'Culqi_Pago/js/view/payment/method-renderer/culqi-method'
            }
        );
        return Component.extend({});
    }
);