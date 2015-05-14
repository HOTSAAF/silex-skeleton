'use strict';

var cutil = require('clam/core/util');
cutil.notation.module.prefix = 'jsc-'; // Clam uses the 'jsm' prefix by default.
// var module = require('./clam_module/module');


// var $ = require('jquery');
var app_util = require('./module/util');
var shame = require('./module/shame');
var global = require('./module/global');
var config_loader = require('./module/config_loader');
var trans = require('./module/trans');

var swal = require('./clam_module/swal');
var block_hider = require('./clam_module/block_hider');

// Clam modules
cutil.createPrototypes(swal);
cutil.createPrototypes(block_hider);

// Standard modules
config_loader.load();

app_util.init();
trans.init();

global.init();
shame.init();
