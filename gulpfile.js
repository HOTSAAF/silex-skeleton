'use strict';
var srcPath              = './front_src';
var destPath             = './web';
var js_dest_dir          = 'js';
var css_dest_dir         = 'css';
var images_dest_dir      = 'images';
var gulp_bower_dest_dir  = 'bower';

console.time('requiring modules');
var gulp       = require('gulp');
var $          = require('gulp-load-plugins')();
var glob       = require('glob');
var path       = require('path');
var browserify = require('browserify');
var source     = require('vinyl-source-stream');
var buffer     = require('vinyl-buffer');
var bower      = require('main-bower-files');
var sprite     = require('css-sprite').stream;
var argv       = require('yargs').argv;
var Q          = require('q');
var notifier   = require('node-notifier');
console.timeEnd('requiring modules');

// Setting up environment
var envs = ['dev', 'prod'];
var env = argv.env || envs[0];
if (envs.indexOf(env) === -1) {
    throw 'The "' + env + '" environment is not supported in this gulpfile.';
}

// Utility classes
function src(path) { return srcPath + '/' + path; }
function dest(path) { return destPath + '/' + path; }

gulp.task('bower', function() {
    return gulp
        .src(bower(), {base: src('bower_components')})
        .pipe(gulp.dest(dest(gulp_bower_dest_dir)));
});

gulp.task('scripts', function() {
    var arrayBundle = function(srcArray) {
        var deferredPromises = [];

        var srcArrayLength = srcArray.length;
        var deferred = null;
        for (var i = srcArrayLength - 1; i >= 0; i--) {
            deferred = Q.defer();
            deferredPromises.push(deferred.promise);

            browserify({
                entries: srcArray[i],
                debug: env === 'dev',
                paths: [src('bower_components')]
            })
            .bundle()
            .on('error', handleErrors)
            .on('end', (function(deferred) {
                deferred.resolve();
            }.bind(this, deferred)))
            .pipe(source(path.basename(srcArray[i])))
            .pipe(buffer())
            // Mangling sometimes screwed up the browserified modules.
            .pipe($.if(env === 'prod', $.uglify({mangle: false})))
            .pipe(gulp.dest(dest(js_dest_dir)));
        }

        return deferredPromises;
    };

    var bundleDeferred = Q.defer();
    glob(src('scripts/*.js'), {}, function(er, files) {
        Q.all(arrayBundle(files)).then(function() {
            bundleDeferred.resolve();
        });
    });

    return bundleDeferred.promise;
});

gulp.task('styles', function () {
    return gulp
        .src(src('styles/*.scss'))
        .pipe($.if(env === 'dev', $.sourcemaps.init()))
        .pipe($.sass({
            includePaths: [src('bower_components')],
            imagePath: '../' + images_dest_dir
        }))
        .on('error', handleErrors)
        .pipe($.autoprefixer({browsers: ['> 1%']}))
        .pipe($.if(env === 'dev', $.sourcemaps.write()))
        .pipe($.if(env !== 'dev', $.csso()))
        .pipe(gulp.dest(dest(css_dest_dir)));
});


gulp.task('sprites', function () {
    var runSpriteBuild = function(folderPath, spriteName, processor) {
        var conf = {
            name: spriteName,
            style: spriteName + '-' + processor + '.scss',
            cssPath: '../' + images_dest_dir + '/sprites/',
            processor: processor,
            prefix: spriteName
        };

        if (processor == 'css') {
            conf.template = src('sprites/css.mustache');
        }

        var deferred = Q.defer();

        gulp
            .src(folderPath + '*.png')
            .pipe(sprite(conf))
            .pipe($.if(
                '*.png',
                gulp.dest(dest(images_dest_dir + '/sprites')).on('end', function() {deferred.resolve();}),
                gulp.dest(src('styles/sprites/')).on('end', function() {deferred.resolve();})
            ));

        return deferred.promise;
    };

    var buildDeferred = Q.defer();
    glob(src('sprites/*/'), {}, function(er, folders) {
        var deferredPromises = [];
        var foldersLength = folders.length;
        for (var i = foldersLength - 1; i >= 0; i--) {
            var spriteName = path.basename(folders[i]);
            deferredPromises.push(runSpriteBuild(folders[i], spriteName, 'css'));
            deferredPromises.push(runSpriteBuild(folders[i], spriteName, 'scss'));
        };

        Q.all(deferredPromises).then(function() {
            buildDeferred.resolve();
        });
    });

    return buildDeferred.promise;
});

gulp.task('optimize-images', function () {
    return gulp
        .src(dest('images/**/*'))
        .pipe($.imagemin({
            progressive: true,
            interlaced: true
        }))
        .pipe(gulp.dest(dest(images_dest_dir)));
});

gulp.task('watch', function() {
    $.watch(src('styles/**/*.scss'), function () {
        gulp.start('styles');
    });

    $.watch(src('scripts/**/*.js'), function () {
        gulp.start('scripts');
    });

    $.watch(src('sprites/**/*.png'), function () {
        gulp.start('sprites');
    });

    $.watch(dest('images/**/*'), function () {
        //
        notifier.notify({
            'title': 'Reminder',
            'message': 'Don\'t forget to optimize the images you just added!'
        });
    });
});

gulp.task('build', ['bower', 'scripts', 'sprites', 'images'], function() {
    gulp.start('styles');
});

gulp.task('default', ['build']);

function handleErrors() {
    var args = Array.prototype.slice.call(arguments);

    // Send error to notification center with gulp-notify
    $.notify.onError({
        title: 'Compile Error',
        message: '<%= error.message %>'
    }).apply(this, args);

    // Keep gulp from hanging on this task
    this.emit('end');
};
