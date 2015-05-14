'use strict';
var $ = require('jquery');

module.exports = {
    config: {},

    load: function() {
        var content = $('#js-config-loader');
        if (!content.length) {
            console.warn('There is no "#js-config-loader" config container available.');

            return;
        }

        try {
            content = JSON.parse(content.text());
        } catch (e) {
            console.warn('The contents of "#js-config-loader" could not be parsed as a JSON object.', e);

            return;
        }

        this.config = content;
    },

    get: function(key) {
        if (this.config === null) {
            console.warn('Configuration is not loaded yet.')

            return {};
        }

        if (typeof this.config[key] === 'undefined') {
            console.warn('The configuration key "' + key + '" is not available.');
            return {};
        }

        return this.config[key];
    }
};
