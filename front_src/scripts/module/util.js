'use strict';
// var $ = require('jquery');
var data_loader = require('./data_loader');

module.exports = {
    exposedData: {},

    init: function() {
        this.exposedData = data_loader.get('util');
    },

    get: function(key) {
        return this.exposedData[key];
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
