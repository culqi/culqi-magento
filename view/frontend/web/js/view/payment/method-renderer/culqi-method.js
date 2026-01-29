define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url',
        'Magento_Ui/js/modal/alert',
        'Magento_Customer/js/customer-data'
    ],
    function (Component, $, fullScreenLoader, urlBuilder, alert, customerData) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Culqi_Pago/payment/culqi'
            },
            
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            
            /**
             * Place order action - overridden to show iframe instead of redirecting
             */
            placeOrder: function (data, event) {
                var self = this;
                
                if (event) {
                    event.preventDefault();
                }
                
                if (this.validate() && this.isPlaceOrderActionAllowed() === true) {
                    this.isPlaceOrderActionAllowed(false);
                    
                    // First, place the order via parent method
                    this.getPlaceOrderDeferredObject()
                        .done(function () {
                            // Order placed successfully, now create payment session
                            self.createPaymentSession();
                        })
                        .fail(function () {
                            self.isPlaceOrderActionAllowed(true);
                        });
                    
                    return true;
                }
                
                return false;
            },
            
            /**
             * Create payment session and show iframe
             */
            createPaymentSession: function () {
                var self = this;
                
                fullScreenLoader.startLoader();
                
                $.ajax({
                    url: urlBuilder.build('pago/payment/createsession'),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        fullScreenLoader.stopLoader();
                        
                        if (response.success && response.redirect_url) {
                            self.showPaymentModal(response.redirect_url, response.payment_methods);
                        } else {
                            alert({
                                title: 'Error',
                                content: response.error || 'Unable to create payment session'
                            });
                            self.isPlaceOrderActionAllowed(true);
                        }
                    },
                    error: function () {
                        fullScreenLoader.stopLoader();
                        alert({
                            title: 'Error',
                            content: 'An error occurred while processing your payment'
                        });
                        self.isPlaceOrderActionAllowed(true);
                    }
                });
            },
            
            /**
             * Show payment modal with iframe
             */
            showPaymentModal: function (gatewayUrl, paymentMethods) {
                var self = this;
                
                // Create modal HTML with loading overlay
                var modalHtml = '<div id="culqi-payment-modal" style="' +
                    'position: fixed;' +
                    'top: 0;' +
                    'left: 0;' +
                    'width: 100%;' +
                    'height: 100%;' +
                    'z-index: 10000;' +
                    '">' +
                    '<div id="culqi-loading-overlay" style="' +
                    'position: absolute;' +
                    'top: 0;' +
                    'left: 0;' +
                    'width: 100%;' +
                    'height: 100%;' +
                    'background: rgba(0, 0, 0, 0.8);' +
                    'display: flex;' +
                    'align-items: center;' +
                    'justify-content: center;' +
                    'z-index: 10001;' +
                    '">' +
                    '<div style="color: white; font-size: 18px;">Cargando...</div>' +
                    '</div>' +
                    '<iframe id="culqi-payment-iframe" src="' + gatewayUrl + '" style="' +
                    'width: 100%;' +
                    'height: 100%;' +
                    'border: none;' +
                    'display: block;' +
                    '"></iframe>' +
                    '</div>';
                
                // Add modal to page
                $('body').append(modalHtml);
                
                // Hide loading overlay when iframe loads
                $('#culqi-payment-iframe').on('load', function () {
                    $('#culqi-loading-overlay').fadeOut(300, function () {
                        $(this).remove();
                    });
                });
                
                // ESC key handler
                $(document).on('keyup.culqimodal', function (e) {
                    if (e.key === 'Escape') {
                        self.closePaymentModal();
                    }
                });
                
                // Listen for postMessage from iframe
                window.addEventListener('message', function (event) {
                    if (event.data && event.data.redirectUrl) {
                        // Payment completed, redirect to registersuccess then success page
                        $.ajax({
                            url: urlBuilder.build('pago/payment/registersuccess'),
                            type: 'POST',
                            dataType: 'json',
                            success: function () {
                                window.location.href = urlBuilder.build('pago/payment/success');
                            }
                        });
                    } else if (event.data && event.data.action === 'closeModal') {
                        // Payment cancelled - restore cart and redirect back to checkout
                        $.ajax({
                            url: urlBuilder.build('pago/payment/restorecart'),
                            type: 'POST',
                            dataType: 'json',
                            success: function () {
                                // Reload cart section to update counter
                                var sections = ['cart'];
                                customerData.invalidate(sections);
                                customerData.reload(sections, true);
                                self.closePaymentModal();
                                window.location.href = urlBuilder.build('checkout');
                            },
                            error: function () {
                                self.closePaymentModal();
                                window.location.href = urlBuilder.build('checkout');
                            }
                        });
                    }
                });
            },
            
            /**
             * Close payment modal
             */
            closePaymentModal: function () {
                $('#culqi-payment-modal').remove();
                $(document).off('keyup.culqimodal');
                this.isPlaceOrderActionAllowed(true);
            }
        });
    }
);