'use strict';
// var $ = require('jquery');
var config_loader = require('./config_loader');

module.exports = {
    config: {},

    init: function() {
        this.config = config_loader.get('util');
    },

    get: function(key) {
        return this.config[key];
    },

    url: function(relativePath) {
        return this.get('baseurl') + '/' + relativePath;
    },

    asset: function(assetPath, noVersion) {
        return this.get('basepath') + '/' + assetPath + (!noVersion ? '?v' + this.get('asset_version') : '');
    },

    // Based on: http://stackoverflow.com/a/11187738/982092
    numberPad: function(number, size) {
        var s = String(number);
        while (s.length < (size || 2)) {
            s = '0' + s;
        }

        return s;
    }
};
