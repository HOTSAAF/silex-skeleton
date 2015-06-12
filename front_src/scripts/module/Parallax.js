'use strict';
var $ = require('jquery');
var mobiledetect = require('./mobiledetect');
var modernizr = require('modernizr');
var dom_config = require('z-dom-config');
var HookFinder = require('z-hook-finder');
var Namespacer = require('z-namespacer');

function Parallax($object) {
    if (mobiledetect.detect('isMobile')) {
        return false;
    }

    this.moduleName = 'parallax';
    this.$object = $object;
    this.finder = new HookFinder(this.$object, 'js-' + this.moduleName + '__');
    this.ns = new Namespacer(this.moduleName);

    this.prefixedTransform = modernizr.prefixed('transform');
    this.$window = $(window);
    this.cachedTransitions = {};

    this.$window.on(this.ns.get('scroll'), this.adjustPositions.bind(this));

    this.adjustPositions();
}

Parallax.prototype.adjustPositions = function() {
    var self = this;
    var scrollTop = this.$window.scrollTop();

    this.finder.find('element').each(function(index) {
        var hookConfig = dom_config.load($(this), 'js-' + self.moduleName);
        if (typeof self.cachedTransitions[index] === 'undefined') {
            self.cachedTransitions[index] = this.style[self.prefixedTransform];
        }

        this.style[self.prefixedTransform] = self.cachedTransitions[index] + ' matrix(1, 0, 0, 1, 0, ' + (scrollTop * hookConfig.yOffset) + ')';
    });
};

// Parallax.prototype.ns = function(eventNames) {
//     var self = this;
//     var eventArray = eventNames.split(' ');
//     eventArray = eventArray.map(function(eventName) {
//         return eventName + '.' + self.module.class.replace('-', '_');
//     });

//     return eventArray.join(' ');
// };

module.exports = Parallax;
