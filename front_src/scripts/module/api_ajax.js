'use strict';
// A wrapper module to combine the backend API, the q library and the jQuery
// ajax call.
// Based on: https://github.com/kriskowal/q/wiki/Coming-from-jQuery
// Note: This solution does not provide access to the XHR object atm.

var $ = require('jquery');
var q = require('q');
// var clam_container = require('clam/core/container');
var app_util = require('./util');
var trans = require('z-trans');

module.exports = {
    getPromise: function(apiUrlPath, jQueryConfig, apiVersion) {
        var defJQueryConfig = {
            type: 'post',
            dataType: 'json'
        };

        if (typeof jQueryConfig !== 'object') {
            jQueryConfig = {};
        }

        jQueryConfig = $.extend({}, defJQueryConfig, jQueryConfig);

        var apiVersions = app_util.get('api_versions');
        var currentApiVersion = app_util.get('current_api_version');
        if (apiVersion === undefined) {
            apiVersion = currentApiVersion;
        }

        // Check if the API version specified is available or not
        if (apiVersions.indexOf(apiVersion) === -1) {
            console.warn('The specified API version (v' + apiVersion + ') is not registered by the application. Using the current version (v' + currentApiVersion + ') instead.');
            apiVersion = currentApiVersion;
        }

        jQueryConfig.url = app_util.url('api/' + apiVersion + '/' + apiUrlPath);

        var promise = q($.ajax(jQueryConfig))
            .then(function(response) {
                if (response.state === 'error') {
                    throw response;
                }

                return response;
            })
            .fail(function(data) {
                // The "data" is most likely a jqXHR object. (Duck typing)
                if ($.isFunction(data.promise)) {
                    if (typeof data.responseJSON !== 'undefined') {
                        throw data.responseJSON;
                    }

                    // Since no responseJSON property were present, the response
                    // is not a valid JSON object. No clue what happened.
                    throw trans.trans('unexpected_error', null, 'errors');
                }

                // The error is neither a standard API error, nor a jqXHR one.
                // This could be caused by a normal exception in a "then" handler.
                // Forwarding it, no extra formatting is needed.
                throw data;
            });

        return promise;
    }
};
