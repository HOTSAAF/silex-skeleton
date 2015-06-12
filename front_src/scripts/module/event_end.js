'use strict';
var $ = require('jquery');

module.exports = {
    $window: null,
    namespace: 'seq',
    timeout: 300,

    init: function() {
        this.$window = $(window);
    },

    onScrollEnd: function(namespace, callback, context, timeout) {
        this.setNamespace(namespace);
        this.setTimeOut(timeout);
        this.addListener('scroll', callback, context);
    },

    onResizeEnd: function(namespace, callback, context, timeout) {
        this.setNamespace(namespace);
        this.setTimeOut(timeout);
        this.addListener('resize', callback, context);
    },

    addListener: function(eventName, callback, context) {
        var self = this;

        if (typeof context === "undefined") {
            context = window;
        }

        var timer;
        this.$window.on(this.getNamespace(eventName), function(e) {
            clearTimeout(timer);
            timer = setTimeout(function() {
                callback.call(context, e);
            }, self.timeout);
        });
    },

    setNamespace: function(namespace) {
        if (typeof namespace === 'undefined')
            return;

        this.namespace = namespace;
    },

    getNamespace: function(eventName) {
        return eventName + '.' + this.namespace;
    },

    setTimeOut: function(timeout) {
        if (typeof timeout === 'undefined')
            return;

        this.timeout = timeout;
    }
};
