'use strict';
var $ = require('jquery');
var api_ajax = require('./api_ajax');
var trans = require('z-trans');
var dom_config = require('z-dom-config');
var Lock = require('z-lock');

module.exports = {
    $obj: null,
    order: null,
    ajax_lock: false,
    config: null,
    moduleName: 'ddsort',

    init: function() {
        var self = this;

        if(!$('.js-ddsort').length || !$('.js-ddsort__element').length) {
            return;
        }

        this.$obj = $("#js-sortable");
        this.config = dom_config.load($('.js-ddsort'), 'js-' + this.moduleName);
        this.ajax_lock = new Lock();

        this.$obj.sortable({
            revert: true
        })
        .disableSelection()
        .on( "sortupdate", function( event, ui ) {
            self.order = self.$obj.sortable('serialize', { key: "sort[]" });
        })
        .on( "sortstop", function( event, ui ) {
            self.sendOrder();
        });
    },

    sendOrder: function() {
        var self = this;

        if (this.ajax_lock.isLocked()) {
            return;
        }

        this.ajax_lock.lock();
        this.$obj.sortable( "disable");

        api_ajax.getPromise(
            this.config.action,
            {data: this.order}
        )
            .then(function(response) {
                console.log('Done.');
            })
            .fail(function(response) {
                alert(trans.trans('unexpected_error', null, 'errors'));
            })
            .fin(function() {
                self.ajax_lock.unLock();
                self.$obj.sortable( "enable");
            })
        ;
    }
};
