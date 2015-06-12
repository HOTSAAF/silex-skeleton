var trans = require('z-trans');
var data_loader = require('./data_loader');

module.exports = {
    config: function() {
        console.log('in');
        // Initializing the trans module
        var transData = data_loader.get('ztrans');
        console.log(transData);
        for (var locale in transData) {
            trans.addData(locale, transData[locale]);
        }
        trans.defaultLocale = 'en'; // Fallback locale
        trans.locale = 'en'; // Current locale
    }
};
