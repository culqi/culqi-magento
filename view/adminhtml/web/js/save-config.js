require(['jquery', 'mage/url', 'mage/translate'], function ($, urlBuilder, $t) {
    console.log('Culqi Config Saver Loaded');
    
    // Extract admin URL from current location
    const currentUrl = window.location.href;
    const adminPathMatch = currentUrl.match(/(https?:\/\/[^\/]+\/[^\/]+)/);
    const adminBaseUrl = adminPathMatch ? adminPathMatch[1] : window.location.origin;
    const configUrl = adminBaseUrl + '/culqi_payment/payment/config';
    
    console.log('Admin Base URL:', adminBaseUrl);
    
    console.log('Config URL:', configUrl);
    
    $('.toggle-payment-method').on('click', function () {
        const methodCode = $(this).data('method-code');
        const status = $(this).data('status'); // 1 for enable, 0 for disable

        console.log('Method Code:', methodCode);
        console.log('Status:', status);

        $.ajax({
            url: configUrl,
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
        console.log('Message received:', event.data);
        
        if (event.data.action === 'saveConfig') {
            const data = event.data.data;

            console.log('Saving config with data:', data);

            $.ajax({
                url: configUrl,
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
                    console.log('Config saved successfully:', response);
                    if (response.success) {
                        // Notify iframe that config was saved
                        event.source.postMessage({
                            action: 'configSaved',
                            success: true
                        }, event.origin);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    // Notify iframe of error
                    event.source.postMessage({
                        action: 'configSaved',
                        success: false,
                        error: error
                    }, event.origin);
                }
            });
        }
        
        if (event.data.action === 'reload') {
            console.log('Reloading page...');
            location.reload();
        }
    }, false);
});
