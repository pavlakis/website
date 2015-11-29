'use strict';
 
var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
 
gulp.task('sass', function () {
  gulp.src('./build/sass/**/+(*.scss|*.sass)')
    .pipe(sourcemaps.init())
    .pipe(sass({outputStyle:'compressed'}).on('error', sass.logError))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./public/css'));
});
 
gulp.task('sass:watch', function () {
  gulp.watch('./build/sass/**/+(*.scss|*.sass)', ['sass']);
});

var paths = {
 scripts: ['bower_components/modernizr/modernizr.js','bower_components/sir-trevor-js/sir-trevor.js','bower_components/jquery/dist/jquery.min.js','bower_components/foundation/js/foundation.min.js','bower_components/foundation/js/foundation/foundation.topbar.js','build/js/app.js'],
 dist: 'public/js/'
};

gulp.task('move', function(){
 gulp.src(paths.scripts)
 .pipe(gulp.dest(paths.dist));
});

var autoprefixer = require('gulp-autoprefixer');

gulp.task('prefix', function () {
    return gulp.src('build/text.sass')
        .pipe(autoprefixer({
            browsers: ['last 2 versions'],
            cascade: false
        }))
        .pipe(gulp.dest('public/css/text.css'));
});
gulp.task('default',['sass:watch','move','prefix']);
