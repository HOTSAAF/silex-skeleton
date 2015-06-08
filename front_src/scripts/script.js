'use strict';

var cutil = require('clam/core/util');
cutil.notation.module.prefix = 'jsc-'; // Clam uses the 'jsm' prefix by default.

// var $ = require('jquery');
var app_util = require('./module/util');
var shame = require('./module/shame');
var global = require('./module/global');
var config_loader = require('./module/config_loader');
var trans_configurator = require('./module/trans_configurator');

var parallax = require('./clam_module/parallax');
var contact_form = require('./clam_module/contact_form');

// This should be the first one to execute, since other modules depend on the
// configurations loaded by this one.
config_loader.load();

// Common and Clam modules. (The order could be important.)
app_util.init();
trans_configurator.config();
cutil.createPrototypes(contact_form);
cutil.createPrototypes(parallax);

// These two should come last.
global.init();
shame.init();
