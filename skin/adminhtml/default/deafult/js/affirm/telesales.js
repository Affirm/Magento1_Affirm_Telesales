/*global window, document, $, Ajax, setLocation */
var AFFIRM_AFFIRM = AFFIRM_AFFIRM || {};
(function () {
    'use strict';
    AFFIRM_AFFIRM.telesales = {
        /**
         * Create and Send Checkout
         *
         * @param string url
         */
        createCheckout: function (url) {
            new Ajax.Request(url, {
                parameters: {},
                onSuccess: function (response) {
                    try {
                        var data = JSON.parse(response.responseText);
                        console.log(data);
                        if (data.success) {
                            console.log('checkout_id: '+data.checkout_id);
                            console.log('redirect_url: ' +data.redirect_url);
                            window.location.reload();
                        } else{
                            alert('Error in sending checkout request to user. Please try again.');
                        }
                    } catch (e) {

                    }
                }
            });
        },
        /**
         * Get and reSend Checkout
         *
         * @param string url
         */
        resendCheckout: function (url) {
            new Ajax.Request(url, {
                parameters: {},
                onSuccess: function (response) {
                    try {
                        var data = JSON.parse(response.responseText);
                        console.log(data);
                        if (data.success) {
                            console.log('checkout_id: '+data.checkout_id);
                            console.log('redirect_url: ' +data.redirect_url);
                            window.location.reload();
                        } else{
                            alert('Error in sending checkout request to user. Please try again.');
                        }
                    } catch (e) {

                    }
                }
            });
        }
    };
})();
