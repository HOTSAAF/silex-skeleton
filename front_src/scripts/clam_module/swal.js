'use strict';
var $ = require('jquery');
var clam_module = require('clam/core/module');
var swal = require('sweet-alert');
// var modifier = require('clam/core/modifier');
// var cutil = require('clam/core/util');
var inherits = require('util').inherits;

var settings = {
    // type: 'singleton',
    conf: {
        swal: {
            title: '',
            text: 'Default text',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            closeOnSwal: false,
            confirmButtonText: 'Igen',
            cancelButtonText: 'Mégsem'
        }
    }
};

function Swal($jQObj, conf) {
    var self = this;
    clam_module.apply(this, [$jQObj, settings, conf]);
    // this.expose();
    this.submitting = false;

    if (this.module.$object.prop("tagName") == "FORM") {
        this.module.$object.on(this.ns('submit'), function(e) {
            if (!self.submitting) {
                e.preventDefault();
                self.swalProxyForm();
            }
        });
    } else {
        this.module.$object.on(this.ns('click'), function(e) {
            e.preventDefault();
            self.swalProxy();
        });
    }
}

inherits(Swal, clam_module);

Swal.prototype.swalProxy = function() {
    var self = this;

    swal(
        this.module.conf.swal,
        function(isConfirm) {
            if (isConfirm) {
                top.location.href = self.module.$object.attr('href');
            }
        }
    );
};

Swal.prototype.swalProxyForm = function() {
    var self = this;

    swal(
        this.module.conf.swal,
        function(isConfirm) {
            if (isConfirm) {
                self.submitting = true;
                self.module.$object.submit();
            }
        }
    );
};

module.exports = Swal;
