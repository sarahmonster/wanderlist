/*!
 * gulp
 * $ npm install gulp-ruby-sass gulp-autoprefixer gulp-minify-css gulp-concat gulp-uglify gulp-notify gulp-livereload gulp-cache del --save-dev
 */

// Load plugins
var gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    uglify = require('gulp-uglify'),
    del = require('del');

gulp.task('sass', function() {
  return gulp.src('./assets/scss/style.scss')
    .pipe(sass({ style: 'expanded' }).on('error', sass.logError))
    .pipe(autoprefixer('last 2 version'))
    .pipe(gulp.dest('./includes/public/css'));
});

gulp.task('clean', function(done) {
  del(['./includes/public/css/style.css'], done);
});

gulp.task('build', ['clean', 'sass']);

gulp.task('watch', function() {

  // Watch .scss files
  gulp.watch('./assets/scss/**/*.scss', ['sass']);

  // Create LiveReload server
  // livereload.listen();

  // Watch any files in dist/, reload on change
  // gulp.watch(['style.css']).on('change', livereload.changed);

});

// Default task is watch
gulp.task('default', ['build', 'watch'])
