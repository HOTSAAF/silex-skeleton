'use strict';
console.time('Setup time');

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

    if (util.env === 'dev') {
        var bower    = require('main-bower-files');
        var sprite   = require('css-sprite').stream;
        var notifier = require('node-notifier');
    }

console.timeEnd('Setup time');

gulp.task('bower', function() {
    return gulp
        .src(bower(), {base: util.src('bower_components')})
        .pipe(gulp.dest(util.dest(util.bowerDestDir)));
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
                debug: util.env === 'dev',
                paths: [util.src('bower_components')]
            })
            .bundle()
            .on('error', util.handleErrors)
            .on('end', (function(deferred) {
                deferred.resolve();
            }.bind(this, deferred)))
            .pipe(source(path.basename(srcArray[i])))
            .pipe(buffer())
            // Mangling sometimes screwed up the browserified modules.
            .pipe($.if(util.env === 'prod', $.uglify({mangle: false})))
            .pipe(gulp.dest(util.dest(util.jsDestDir)));
        }

        return deferredPromises;
    };

    var bundleDeferred = Q.defer();
    glob(util.src('scripts/*.js'), {}, function(er, files) {
        Q.all(arrayBundle(files)).then(function() {
            bundleDeferred.resolve();
        });
    });

    return bundleDeferred.promise;
});

gulp.task('styles', function () {
    return gulp
        .src(util.src('styles/*.scss'))
        .pipe($.if(util.env === 'dev', $.sourcemaps.init()))
        .pipe($.sass({
            includePaths: [util.src('bower_components')],
            imagePath: '../' + util.imagesDestDir
        }))
        .on('error', util.handleErrors)
        .pipe($.autoprefixer({browsers: ['> 1%']}))
        .pipe($.if(util.env === 'dev', $.sourcemaps.write()))
        .pipe($.if(util.env !== 'dev', $.csso()))
        .pipe(gulp.dest(util.dest(util.cssDestDir)));
});


gulp.task('sprites', function () {
    var runSpriteBuild = function(folderPath, spriteName, processor) {
        var conf = {
            name: spriteName,
            style: spriteName + '-' + processor + '.scss',
            cssPath: '../' + util.imagesDestDir + '/sprites/',
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
                gulp.dest(util.dest(util.imagesDestDir + '/sprites')).on('end', function() {deferred.resolve();}),
                gulp.dest(src('styles/sprites/')).on('end', function() {deferred.resolve();})
            ));

        return deferred.promise;
    };

    var buildDeferred = Q.defer();
    glob(util.src('sprites/*/'), {}, function(er, folders) {
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
        .src(util.dest('images/**/*'))
        .pipe($.imagemin({
            progressive: true,
            interlaced: true
        }))
        .pipe(gulp.dest(util.dest(util.imagesDestDir)));
});

gulp.task('watch', function() {
    $.watch(util.src('styles/**/*.scss'), function () {
        gulp.start('styles');
    });

    $.watch(util.src('scripts/**/*.js'), function () {
        gulp.start('scripts');
    });

    $.watch(util.src('sprites/**/*.png'), function () {
        gulp.start('sprites');
    });

    $.watch(util.dest('images/**/*'), function () {
        notifier.notify({
            'title': 'Reminder',
            'message': 'Don\'t forget to optimize the images you just added!'
        });
    });
});

gulp.task('build', ['scripts', 'styles']);

gulp.task('default', ['build']);
