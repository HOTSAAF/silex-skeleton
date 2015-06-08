var trans = require('z-trans');
var config_loader = require('./config_loader');

module.exports = {
    config: function() {
        // Initializing the trans module
        var transData = config_loader.get('ztrans');
        for (var locale in transData) {
            trans.addData(locale, transData[locale]);
        }
        trans.defaultLocale = 'en'; // Fallback locale
        trans.locale = 'en'; // Current locale

        window.trans = trans;
    }
};
