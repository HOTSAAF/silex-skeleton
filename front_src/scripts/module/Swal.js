'use strict';
var swal = require('sweet-alert');
var dom_config = require('z-dom-config');

function Swal($object, conf) {
    var self = this;

    this.$object = $object;
    this.moduleName = 'swal';
    this.ns = new Namespacer(this.moduleName);
    this.config = dom_config.load(this.$object, 'jsc-' + this.moduleName, {
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
    });


    this.tagName = this.$object.prop('tagName');
    if (this.isForm()) {
        this.submitting = false;
        this.$object.on(this.ns.get('submit'), function(e) {
            if (!self.submitting) {
                e.preventDefault();
                self.forwardToPlugin();
            }
        });
    } else {
        this.$object.on(this.ns.get('click'), function(e) {
            e.preventDefault();
            self.forwardToPlugin();
        });
    }
}

Swal.prototype.isForm = function() {
    return this.tagName === 'FORM';
};

Swal.prototype.forwardToPlugin = function() {
    var self = this;

    swal(
        this.config.swal,
        function(isConfirm) {
            if (isConfirm) {
                if (self.isForm()) {
                    self.submitting = true;
                    self.module.$object.submit();
                } else {
                    top.location.href = self.module.$object.attr('href');
                }
            }
        }
    );
};

module.exports = Swal;
