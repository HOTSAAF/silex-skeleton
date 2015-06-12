'use strict';
var $ = require('jquery');
var container = require('z-container');

module.exports = {
    config: {},

    load: function() {
        var content = $('#js-data-loader');
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

        container.add('exposed_data', content);
    },

    get: function(key) {
        if (container.has('exposed_data') === false) {
            console.warn('It seems that exposed data is not loaded yet.')

            return {};
        }

        if (typeof container.get('exposed_data')[key] === 'undefined') {
            console.warn('The exposed data key "' + key + '" is not available.');
            return {};
        }

        return container.get('exposed_data')[key];
    }
};
