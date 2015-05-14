'use strict';
// Proxy for the PHP Mobile Detect library to JavaScript.
// Using classes with a certain prefix on the html element.
module.exports = {
    conf: {
        prefix: 'md_'
    },

    detect: function(libMethod) {
        return document.getElementsByTagName('html')[0].className.indexOf(this.conf.prefix + libMethod) !== -1;
    }
};
