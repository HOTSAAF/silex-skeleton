'use strict';
var $ = require('jquery');
var mobiledetect = require('../module/mobiledetect');
var clam_module = require('clam/core/module');
// var modifier = require('clam/core/modifier');
// var cutil = require('clam/core/util');
var inherits = require('util').inherits;
var modernizr = require('modernizr');

var settings = {
    type: 'singleton',
    hasGlobalHooks: true,
    conf: {}
};

function Parallax($jQObj, conf) {
    var self = this;

    clam_module.apply(this, [$jQObj, settings, conf]);
    //this.expose();

    if (mobiledetect.detect('isMobile')) {
        return false;
    }

    this.prefixedTransform = modernizr.prefixed('transform');
    this.$window = $(window);
    // alert(this.prefixedTransform);

    this.cachedTransitions = {};

    this.$window.on(this.ns('scroll'), function() {
        self.adjustPositions();
    });

    self.adjustPositions();
}

inherits(Parallax, clam_module);

Parallax.prototype.adjustPositions = function() {
    var self = this;
    var scrollTop = this.$window.scrollTop();

    $.each(this.getHooks('element'), function(index) {
        var hookConf = self.getHookConfiguration($(this));
        if (typeof self.cachedTransitions[index] === 'undefined') {
            self.cachedTransitions[index] = this.style[self.prefixedTransform];
        }

        // var cssObj = {};
        // cssObj[self.prefixedTransform] = self.cachedTransitions[index] + 'translateY('+(scrollTop*hookConf.yOffset)+'px)';
        // $(this).css(cssObj);
        // this.style[self.prefixedTransform] = self.cachedTransitions[index] + 'translateY(' + (scrollTop * hookConf.yOffset) + 'px) translateZ(0)';
        this.style[self.prefixedTransform] = self.cachedTransitions[index] + ' matrix(1, 0, 0, 1, 0, ' + (scrollTop * hookConf.yOffset) + ')';
    });
};

Parallax.prototype.ns = function(eventNames) {
    var self = this;
    var eventArray = eventNames.split(' ');
    eventArray = eventArray.map(function(eventName) {
        return eventName + '.' + self.module.class.replace('-', '_');
    });

    return eventArray.join(' ');
};

module.exports = Parallax;
