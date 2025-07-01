require(['jquery', 'mage/url', 'mage/translate'], function ($, urlBuilder, $t) {
    $('.toggle-payment-method').on('click', function () {
        const methodCode = $(this).data('method-code');
        const status = $(this).data('status'); // 1 for enable, 0 for disable

        console.log(methodCode);
        console.log(status);

        $.ajax({
            url: urlBuilder.build('/admin_rqx8uqr/culqi_payment/payment/config'),
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
});
