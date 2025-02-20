require(['jquery', 'mage/url', 'mage/translate'], function ($, urlBuilder, $t) {
    console.log(12345342);
    $('.toggle-payment-method').on('click', function () {
        const methodCode = $(this).data('method-code');
        const status = $(this).data('status'); // 1 for enable, 0 for disable

        console.log(methodCode);
        console.log(status);

        $.ajax({
            url: urlBuilder.build('/admin_uxr9ms/culqi_payment/payment/config'),
            type: 'POST',
            data: {
                method_code: methodCode,
                status: status,
                form_key: window.FORM_KEY 
            },
            showLoader: true,
            success: function (response) {
                if (response.success) {
                    alert($t(response.message));
                    location.reload();
                } else {
                    alert($t(response.message));
                }
            },
            error: function () {
                alert($t('An error occurred while updating the payment method.'));
            }
        });
    });
    window.addEventListener('message', function(event) {
        console.log(event.data);
        if (event.data.action === 'saveConfig') {
            const data = event.data.data;

            $.ajax({
                url: urlBuilder.build('/admin_uxr9ms/culqi_payment/payment/config'),
                type: 'POST',
                data: {
                    pluginStatus: data.pluginStatus,
                    publicKey: data.publicKey,
                    merchant: data.merchant,
                    rsa_pk_culqi: data.rsaPkCulqi,
                    rsa_sk_plugin: data.rsaSkPlugin,
                    payment_methods: data.paymentMethods,
                    method_code: 'culqi',
                    status: true,
                    form_key: window.FORM_KEY 
                },
                success: function(response) {
                    //alert($t(response.message));
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + error);
                }
            });
        }
        //if (event.origin !== 'http://localhost:5173') return;
        if (event.data.action === 'reload') {
            location.reload();
        }
    }, false);
});
