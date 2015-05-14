'use strict';
// This module should contain "global" code, like jQuery plugin calls.
// Code placed here is different than the code placed in the shame.js module,
// since although the code written here is not modular, it is not shameful
// either. It's just the way it works.
var api_ajax = require('./api_ajax');
var image_loader = require('./image_loader');

module.exports = {
    init: function() {
        $(document).foundation({
            equalizer: {
                equalize_on_stack: false
            }
        });

        // Test call
        api_ajax
            .getPromise('', {type: 'get'})
            .then(function(response) {
                console.log('SUCCESS,', response.response);
            })
            .fail(function(err) {
                // Possible catch here: every exception occuring in "then" handlers
                // will be forwarded here. (Ex.: Accessing an undefined property.)
                if (typeof err.response !== 'undefined') {
                    console.log('API ERROR,', err.response);
                } else {
                    console.log('UNKNOWN ERROR,', err);
                }
            });

        // Image Loader example
        if ($('.jsc-parallax__element').length) {
            image_loader
                .getPromise($('.jsc-parallax__element'))
                .fin(function() {
                    $('#load').css('opacity', '1');
                });
        }
    }
};
