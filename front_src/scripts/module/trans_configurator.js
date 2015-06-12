var trans = require('z-trans');
var data_loader = require('./data_loader');

module.exports = {
    config: function() {
        // Initializing the trans module
        var transData = data_loader.get('ztrans');
        for (var locale in transData) {
            trans.addData(locale, transData[locale]);
        }
        trans.defaultLocale = 'en'; // Fallback locale
        trans.locale = 'en'; // Current locale
    }
};
