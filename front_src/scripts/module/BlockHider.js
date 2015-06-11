'use strict';
var dom_config = require('z-dom-config');
var HookFinder = require('z-hook-finder');

function BlockHider($object) {
    this.$object = $object;
    this.moduleName = 'block-hider';
    this.config = dom_config.load(this.$object, 'jsc-' + this.moduleName, {
        hideSpeed: 300
    });
    this.HookFinder = new HookFinder(this.$object, 'jsc-' + this.moduleName + '__');

    this.HookFinder.find('close-btn', 1).on('click', this.hide.bind(this));
}

BlockHider.prototype.hide = function() {
    this.$object.hide(this.config.hideSpeed);
};

module.exports = BlockHider;
