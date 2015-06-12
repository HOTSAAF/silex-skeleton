'use strict';
var api_ajax = require('../module/api_ajax');
var trans = require('z-trans');
var grecaptcha = require('grecaptcha');
var data_loader = require('../module/data_loader');
var HookFinder = require('z-hook-finder');
var Lock = require('z-lock');
var Namespacer = require('z-namespacer');

function ContactForm($object) {
    var self = this;

    this.exposedData = data_loader.get('contact_form');

    this.$object = $object;
    this.moduleName = 'contact-form';
    this.finder = new HookFinder(this.$object, 'js-' + this.moduleName + '__');
    this.animationLock = new Lock();
    this.ns = new Namespacer(this.moduleName);

    this.$form = this.$object.find('form');
    this.$form.on(this.ns.get('submit'), this.onFormSubmit.bind(this));

    this.$inputs = this.$form.find('[name]').addBack('[name]');

    // Initializing reCAPTCHA widget.
    // We must render it manually, so that we can save the created widget, which
    // can used later as the "opt_widget_id" for resetting.
    if (!this.exposedData.recaptcha_site_key) {
        console.error('Missing reCaptcha site key.');
    } else {
        this.reCaptchaWidget = grecaptcha.render(
            this.finder.find('recaptcha', 1)[0],
            {
                sitekey: this.exposedData.recaptcha_site_key
            }
        );
    }
}

ContactForm.prototype.onFormSubmit = function(e) {
    var self = this;
    e.preventDefault();

    if (this.animationLock.isLocked()) {
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
    this.animationLock.lock();
    this.$form.find('[type=submit]')
        .prop('disabled', true)
        .text(trans.trans('label_send_btn', null, 'contact_form') + '...')
    ;
};

ContactForm.prototype.unlock = function() {
    this.animationLock.unLock();
    this.$form.find('[type=submit]')
        .prop('disabled', false)
        .text(trans.trans('label_send_btn', null, 'contact_form') + '')
    ;
};

ContactForm.prototype.removeAllErrors = function() {
    // Remove all "ul" tags before all elements that has the "name" attribute
    this.$inputs.next('ul').remove();

    // An exception for reCAPTCHA
    this.finder.find('recaptcha', 1).next('ul').remove();
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
        $field = this.finder.find('recaptcha', 1);
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
