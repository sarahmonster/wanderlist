// Load plugins
var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var minifycss = require('gulp-minify-css');
var uglify = require('gulp-uglify');
var del = require('del');

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
});

// Default task is watch
gulp.task('default', ['build', 'watch'])
