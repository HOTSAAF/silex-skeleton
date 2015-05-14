'use strict';
var config_loader = require('./config_loader');
// var $ = require('jquery');

module.exports = {
    transData: {},

    init: function () {
        this.transData = config_loader.get('trans');
        window.trans = this;
    },

    trans: function(key, data, domain) {
        if (
            typeof this.transData[domain] === 'undefined' ||
            typeof this.transData[domain][key] === 'undefined'
        ) {
            return key;
        }

        if (typeof data !== 'object') {
            data = {};
        }

        var transString = this.transData[domain][key];
        for (var index in data) {
            transString = transString.replace(index, data[index]);
        }

        return transString;
    }
};
