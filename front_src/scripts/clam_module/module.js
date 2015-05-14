// Created by using the https://gist.github.com/ZeeCoder/29c67fd326311e40ff38
// sublime text snippet.
'use strict';
var clam_module = require('clam/core/module');
// var clam_container = require('clam/core/container');
// var modifier = require('clam/core/modifier');
// var $ = require('jquery');
var inherits = require('util').inherits;

var settings = {
    // type: 'singleton',
    // hasGlobalHooks: true,
    conf: {
    }
};

function Module($jQObj, conf) {
    var self = this;
    clam_module.apply(this, [$jQObj, settings, conf]);
    // this.expose();
    // throw this.prettify('error');
    // clam_container.get('clam-module');

    // this.getHooks('module');
}

inherits(Module, clam_module);

// Module.prototype.boiler = function() {
// };

module.exports = Module;
