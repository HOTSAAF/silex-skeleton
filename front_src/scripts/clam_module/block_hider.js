// Created by using the https://gist.github.com/ZeeCoder/29c67fd326311e40ff38
// sublime text snippet.
'use strict';
var clam_module = require('clam/core/module');
// var clam_container = require('clam/core/container');
// var modifier = require('clam/core/modifier');
var $ = require('jquery');
var inherits = require('util').inherits;

var settings = {
    // type: 'singleton',
    // hasGlobalHooks: true,
    conf: {
        hideSpeed: 300
    }
};

function BlockHider($jQObj, conf) {
    var self = this;
    clam_module.apply(this, [$jQObj, settings, conf]);
    // this.expose();
    // throw this.prettify('error');
    // clam_container.get('clam-module');

    this.getHook('close-btn').on(this.ns('click'), function() {
        self.hide();
    });
}

inherits(BlockHider, clam_module);

BlockHider.prototype.hide = function() {
    this.module.$object.hide(this.module.conf.hideSpeed);
};

module.exports = BlockHider;
