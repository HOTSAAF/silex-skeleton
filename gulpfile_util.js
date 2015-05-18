'use strict';
var argv = require('yargs').argv;

module.exports = {
    env:           argv.env || 'dev',
    availableEnvs: ['dev', 'prod'],

    srcPath:       './front_src',
    destPath:      './web',

    jsDestDir:     'js',
    cssDestDir:    'css',
    imagesDestDir: 'images',
    bowerDestDir:  'bower',

    src: function(path) {
        return this.srcPath + '/' + path;
    },

    dest: function(path) {
        return this.destPath + '/' + path;
    },

    checkEnv: function() {
        if (this.availableEnvs.indexOf(this.env) === -1) {
            throw 'The "' + env + '" environment is not supported in this gulpfile.';
        }
    },

    handleErrors: function() {
        var args = Array.prototype.slice.call(arguments);

        // Send error to notification center with gulp-notify
        require('gulp-notify').onError({
            title: 'Compile Error',
            message: '<%= error.message %>'
        }).apply(this, args);

        // Keep gulp from hanging on this task
        this.emit('end');
    }
};
