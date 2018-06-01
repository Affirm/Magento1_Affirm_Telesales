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
                            window.location.reload();
                        } else if(data.error && data.message){
                            alert(data.message);
                        } else {
                            alert('The Affirm checkout link was not sent to the customer due to an error. Please try again.');
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
                            window.location.reload();
                        } else if(data.error && data.message){
                            alert(data.message);
                        } else {
                            alert('The Affirm checkout link was not sent to the customer due to an error. Please try again.');
                        }
                    } catch (e) {

                    }
                }
            });
        }
    };
})();
