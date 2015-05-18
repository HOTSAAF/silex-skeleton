'use strict';

var util = require('./gulpfile_util');
util.checkEnv();

var gulp       = require('gulp');
var $          = require('gulp-load-plugins')();
var glob       = require('glob');
var path       = require('path');
var browserify = require('browserify');
var source     = require('vinyl-source-stream');
var buffer     = require('vinyl-buffer');
var Q          = require('q');
// var es         = require('event-stream');
var merge      = require('merge-stream');
var del        = require('del');

if (util.env === 'dev') {
    var sprite   = require('css-sprite').stream;
    var notifier = require('node-notifier');
}

/**
 * A simple copy task mainly designed to copy bower components under the public
 * directory. Depends on the configuration provided by the
 * `util.getCopyConfiguration` call.
 */
gulp.task('copy', function() {
    var copyConfigurations = util.getCopyConfiguration();
    var deferred = Q.defer();
    var copyTaskNum = copyConfigurations.length;
    var copyTaskDoneCounter = 0;

    if (copyTaskNum > 0) {
        copyConfigurations.forEach(function(config) {
            gulp
                .src(config.src)
                .pipe(gulp.dest(config.dest))
                .on('end', function() {
                    copyTaskDoneCounter++;
                    if (copyTaskDoneCounter >= copyTaskNum) {
                        deferred.resolve();
                    }
                });
        });
    } else {
        deferred.resolve();
    }

    return deferred.promise;
});

/**
 * This task creates browserify bundles, and concatenates them with additional
 * scripts, like bower components. Depends on the `util.getScriptsConfiguration`
 * call to determine what the bundles need to be concatenated with.
 * Note: The bundle scripts will always be the last script appended to the
 * other files.
 */
gulp.task('build-scripts', function() {
    var filesArray = glob.sync(util.src('scripts/*.js'));
    var filesArrayLength = filesArray.length;
    var browserifyStreams = {};
    var scriptName = null;
    var deferred = Q.defer();
    var scriptConfiguration = util.getScriptsConfiguration();
    var scriptsDoneCounter = 0;

    del.sync(util.dest(util.jsDestDir));

    for (var i = filesArrayLength - 1; i >= 0; i--) {
        scriptName = path.basename(filesArray[i]);
        browserifyStreams[scriptName] = browserify({
            entries: filesArray[i],
            debug: util.env === 'dev',
            paths: [util.src('bower_components')]
        })
        .bundle()
        .on('error', util.handleErrors)
        .pipe(source(scriptName))
        .pipe(buffer());
    }

    Object.keys(browserifyStreams).forEach(function(scriptName) {
        if (typeof scriptConfiguration[scriptName] === 'undefined') {
            scriptConfiguration[scriptName] = [];
        }
    });

    var scriptsNum = Object.keys(scriptConfiguration).length;

    Object.keys(scriptConfiguration).forEach(function(scriptName) {
        var stream = null;
        if (typeof browserifyStreams[scriptName] !== 'undefined') {
            stream = merge(
                gulp.src(scriptConfiguration[scriptName]),
                browserifyStreams[scriptName]
            );
        } else {
            stream = gulp.src(scriptConfiguration[scriptName]);
        }

        stream
            .pipe($.concat(scriptName))
            // Mangling sometimes screwed up the browserified modules.
            .pipe($.if(util.env === 'prod', $.uglify({mangle: false})))
            .pipe(gulp.dest(util.dest(util.jsDestDir + '/')))
            .on('end', function() {
                scriptsDoneCounter++;
                $.util.log('Generated ' + $.util.colors.magenta(scriptName) + '...');
                if (scriptsDoneCounter >= scriptsNum) {
                    deferred.resolve();
                }
            });
    });

    return deferred.promise;
});

gulp.task('build-styles', function () {
    var filesArray = glob.sync(util.src('styles/[^_]*.scss'));
    var filesArrayLength = filesArray.length;
    var styleName = null;
    var sassStreams = {};
    var deferred = Q.defer();
    var stylesConfiguration = util.getStylesConfiguration();
    var stylesDoneCounter = 0;

    del.sync(util.dest(util.cssDestDir));

    for (var i = filesArrayLength - 1; i >= 0; i--) {
        styleName = path.basename(filesArray[i]).replace('scss', 'css');
        sassStreams[styleName] = gulp
            .src(filesArray[i])
            .pipe($.if(util.env === 'dev', $.sourcemaps.init()))
            .pipe($.sass({
                includePaths: [util.src('bower_components')],
                imagePath: '../' + util.imagesDestDir
            }))
            .on('error', util.handleErrors)
            .pipe($.autoprefixer({browsers: ['> 1%']}))
            .pipe($.if(util.env === 'dev', $.sourcemaps.write()));
    }

    Object.keys(sassStreams).forEach(function(styleName) {
        if (typeof stylesConfiguration[styleName] === 'undefined') {
            stylesConfiguration[styleName] = [];
        }
    });

    var stylesNum = Object.keys(stylesConfiguration).length;

    Object.keys(stylesConfiguration).forEach(function(styleName) {
        var stream = null;
        if (typeof sassStreams[styleName] !== 'undefined') {
            stream = merge(
                gulp.src(stylesConfiguration[styleName]),
                sassStreams[styleName]
            );
        } else {
            stream = gulp.src(stylesConfiguration[styleName]);
        }

        stream
            .pipe($.concat(styleName))
            .pipe($.if(util.env !== 'dev', $.csso()))
            .pipe(gulp.dest(util.dest(util.cssDestDir + '/')))
            .on('end', function() {
                stylesDoneCounter++;
                $.util.log('Generated ' + $.util.colors.magenta(styleName) + '...');
                if (stylesDoneCounter >= stylesNum) {
                    deferred.resolve();
                }
            });
    });

    return deferred.promise;
});

gulp.task('build-sprites', function () {
    var generateSprites = function(folderPath, spriteName, processor) {
        var conf = {
            name: spriteName,
            style: spriteName + '-' + processor + '.scss',
            cssPath: '../' + util.imagesDestDir + '/sprites/',
            processor: processor,
            prefix: spriteName
        };

        if (processor == 'css') {
            conf.template = util.src('sprites/css.mustache');
        }

        var deferred = Q.defer();

        gulp
            .src(folderPath + '*.png')
            .pipe(sprite(conf))
            .pipe($.if(
                '*.png',
                gulp.dest(util.dest(util.imagesDestDir + '/sprites')).on('end', function() {deferred.resolve();}),
                gulp.dest(util.src('styles/sprites/')).on('end', function() {deferred.resolve();})
            ));

        return deferred.promise;
    };

    var spriteFolders = glob.sync(util.src('sprites/*/'));

    var promises = [];
    var spriteFoldersLength = spriteFolders.length;
    for (var i = spriteFoldersLength - 1; i >= 0; i--) {
        var spriteName = path.basename(spriteFolders[i]);
        promises.push(generateSprites(spriteFolders[i], spriteName, 'css'));
        promises.push(generateSprites(spriteFolders[i], spriteName, 'scss'));
    };

    return Q.all(promises);
});

gulp.task('optimize-images', function () {
    return gulp
        .src(util.dest('images/**/*'))
        .pipe($.imagemin({
            progressive: true,
            interlaced: true
        }))
        .pipe(gulp.dest(util.dest(util.imagesDestDir)));
});

gulp.task('watch', function() {
    $.watch(util.src('styles/**/*.scss'), function () {
        gulp.start('build-styles');
    });

    $.watch(util.src('scripts/**/*.js'), function () {
        gulp.start('build-scripts');
    });

    $.watch(util.src('sprites/**/*.png'), function () {
        gulp.start('build-sprites');
    });

    $.watch(util.dest('images/**/*'), function () {
        notifier.notify({
            'title': 'Reminder',
            'message': 'Don\'t forget to optimize the images you just added!'
        });
    });
});

gulp.task('build', ['build-scripts', 'build-styles']);

gulp.task('default', ['build']);
