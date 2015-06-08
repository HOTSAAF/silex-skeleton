'use strict';
var clam_module = require('clam/core/module');
// var clam_container = require('clam/core/container');
// var modifier = require('clam/core/modifier');
// var $ = require('jquery');
var inherits = require('util').inherits;
var api_ajax = require('../module/api_ajax');
var trans = require('z-trans');
var grecaptcha = require('grecaptcha');
var config_loader = require('../module/config_loader');

var settings = {
    type: 'singleton',
    // hasGlobalHooks: true,
    conf: {}
};

function ContactForm($jQObj, conf) {
    var self = this;
    clam_module.apply(this, [$jQObj, settings, conf]);
    // this.expose();
    // throw this.prettify('error');
    // clam_container.get('clam-module');

    this.config = config_loader.get('contact_form');

    this.$form = this.module.$object.find('form');
    this.$form.on(this.ns('submit'), function(e) {
        self.onFormSubmit(e);
    });

    this.$inputs = this.$form.find('[name]').addBack('[name]');

    // Initializing reCAPTCHA widget.
    // We must render it manually, so that we can save the created widget, which
    // can used later as the "opt_widget_id" for resetting.
    this.reCaptchaWidget = grecaptcha.render(
        this.getHook('recaptcha')[0],
        {
            sitekey: this.config.recaptcha_site_key
        }
    );
}

inherits(ContactForm, clam_module);

ContactForm.prototype.onFormSubmit = function(e) {
    var self = this;
    e.preventDefault();

    if (this.locked) {
        return;
    }

    this.removeAllErrors();
    this.lock();

    api_ajax.getPromise(
        this.$form.attr('action'),
        {data: this.$form.serialize()}
    )
        .then(function(response) {
            self.$form[0].reset();
            alert(response.response);
        })
        .fail(function(response) {
            if (typeof response.response.contact_form !== 'undefined') {
                // Form error
                self.populateFormWithErrors(response.response.contact_form);
                // If any error occurs, the reCAPTCHA, widget must be resetted
                grecaptcha.reset(self.reCaptchaWidget);
            } else {
                // Adding "unknown error" as a global form error.
                self.addErrorsToField(self.$form.attr('name'), [trans.trans('unexpected_error', null, 'errors')]);
            }
        })
        .fin(function() {
            // In spite of wheter the e-mail sending was successful or not,
            // the reCAPTCHA must be resetted.
            // This is due to the fact, that if a valid recaptcha response was
            // used once for validation on the backend, it won't be accepted a
            // second time.
            grecaptcha.reset(self.reCaptchaWidget);

            self.unlock();
        })
    ;
};

ContactForm.prototype.lock = function() {
    this.locked = true;
    this.$form.find('[type=submit]')
        .prop('disabled', true)
        .text(trans.trans('label_send_btn', null, 'contact_form') + '...')
    ;
};

ContactForm.prototype.unlock = function() {
    this.locked = false;
    this.$form.find('[type=submit]')
        .prop('disabled', false)
        .text(trans.trans('label_send_btn', null, 'contact_form') + '')
    ;
};

ContactForm.prototype.removeAllErrors = function() {
    // Remove all "ul" tags before all elements that has the "name" attribute
    this.$inputs.next('ul').remove();

    // An exception for reCAPTCHA
    this.getHook('recaptcha').next('ul').remove();
};

ContactForm.prototype.populateFormWithErrors = function(formErrors) {
    // Adding global errors if present.
    if (typeof formErrors.errors !== 'undefined') {
        this.addErrorsToField(this.$form.attr('name'), formErrors.errors);
    }

    // Adding errors for sub-fields
    if (typeof formErrors.children !== 'undefined') {
        for (var fieldName in formErrors.children) {
            this.addErrorsToField(fieldName, formErrors.children[fieldName].errors);
        }
    }
};

ContactForm.prototype.addErrorsToField = function(fieldName, errors) {
    if (!(errors instanceof Array)) {
        return;
    }

    var $field = null;
    if (fieldName == 'recaptcha_response') {
        // An exception for reCAPTCHA
        $field = this.getHook('recaptcha');
    } else if (fieldName === this.$form.attr('name')) {
        // If the "field" is the form itself (Global errors)
        $field = this.$form;
    } else {
        // Normal field errors
        $field = this.$form.find('[name="' + this.$form.attr('name') + '[' + fieldName + ']"]');
    }

    var $errorList = $('<ul/>').addClass('c-contact-form__error-list');
    var errorsLength = errors.length;
    if (errorsLength === 1) {
        $errorList.addClass('c-contact-form__error-list--single');
    }
    for (var i = 0; i < errorsLength; i++) {
        $errorList.append('<li>' + errors[i] + '</li>');
    }

    $field.after($errorList);
};

module.exports = ContactForm;
