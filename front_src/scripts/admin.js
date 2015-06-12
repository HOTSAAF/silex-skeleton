'use strict';

var app_util = require('./module/util');
var global = require('./module/global');
var config_loader = require('./module/config_loader');
var trans_configurator = require('./module/trans_configurator');
var event_end = require('./module/event_end');
var ddsort = require('./module/ddsort');

// This should be the first one to execute, since other modules depend on the
// configurations loaded by this one.
config_loader.load();

var Swal = require('./module/Swal');
$('.js-swal').each(function() {
    new Swal($(this));
});

var BlockHider = require('./module/BlockHider');
$('.js-block-hider').each(function() {
    new BlockHider($(this));
});

var Preview = require('./module/Preview');
$('.js-preview').each(function() {
    new Preview($(this));
});

trans_configurator.config();
app_util.init();
event_end.init();
ddsort.init();

global.init();
