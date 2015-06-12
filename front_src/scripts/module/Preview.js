'use strict';
var $ = require('jquery');
var mobiledetect = require('./mobiledetect');
var HookFinder = require('z-hook-finder');
var Namespacer = require('z-namespacer');
var dom_config = require('z-dom-config');

function Preview($object) {
    if (mobiledetect.detect('isMobile')) {
        return false;
    }

    var self = this;
    var settings = {
        animation_speed: 'normal',
        slideshow: 3500,
        theme: 'dark_square', /* light_rounded / dark_rounded / light_square / dark_square / facebook */
        social_tools: '',
        overlay_gallery: true
    };

    this.moduleName = 'preview';
    this.$object = $object;
    this.ns = new Namespacer(this.moduleName);
    this.config = dom_config.load(this.$object, 'js-' + this.moduleName, settings);
    this.finder = new HookFinder(this.$object, 'js-' + this.moduleName + '__');

    if (typeof this.config.paged !== 'undefined' && this.config.paged) {
        this.finder.find('element').prettyPhoto(
            this.config
        );
    } else {
        $.fn.prettyPhoto(this.config);

        this.finder.find('element').on(this.ns.get('click'), function(e) {
            e.preventDefault();
            self.openImage($(this));
        });
    }
}

Preview.prototype.openImage = function ($obj) {
    var hookConfig = dom_config.load($obj, 'js-' + this.moduleName);

    if (typeof hookConfig.src == 'undefined') {
        console.warn('"src" data not available!');
        hookConfig.src = '';
    }

    if (typeof hookConfig.title == 'undefined') {
        hookConfig.title = '';
    }

    if (typeof hookConfig.desc == 'undefined') {
        hookConfig.desc = '';
    }

    $.prettyPhoto.open(hookConfig.src, hookConfig.title, hookConfig.desc);
};

module.exports = Preview;
