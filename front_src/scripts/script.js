'use strict';

var app_util = require('./module/util');
var global = require('./module/global');
var config_loader = require('./module/config_loader');
var trans_configurator = require('./module/trans_configurator');
var ContactForm = require('./module/ContactForm');

// This should be the first one to execute, since other modules depend on the
// configurations loaded by this one.
config_loader.load();

var Parallax = require('./module/Parallax');
$('.js-parallax').each(function() {
    new Parallax($(this));
});

var ContactForm = require('./module/ContactForm');
$('.js-contact-form').each(function() {
    new ContactForm($(this));
});

var FileValidator = require('z-file-validator');
new FileValidator(
    $('.js-contact-form .js-file-validator'),
    'js-file-validator',
    function(validationCode, file, config) {
        if (validationCode === true) {
            // The input file is valid, so let's show the file's name in the label element
            validationCode = file.name;
        }

        $(this).next().text(validationCode);
    }
);

trans_configurator.config();
app_util.init();
global.init();
