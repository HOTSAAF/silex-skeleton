'use strict';

var app_util = require('./module/util');
var global = require('./module/global');
var config_loader = require('./module/config_loader');
var trans_configurator = require('./module/trans_configurator');

// This should be the first one to execute, since other modules depend on the
// configurations loaded by this one.
config_loader.load();

var Swal = require('./module/Swal');
$('.jsc-swal').each(function() {
    new Swal($(this));
});

var BlockHider = require('./module/BlockHider');
$('.jsc-block-hider').each(function() {
    new BlockHider($(this));
});

trans_configurator.config();
app_util.init();
global.init();
