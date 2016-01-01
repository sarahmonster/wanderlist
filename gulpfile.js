// Load plugins
var gulp = require( 'gulp' );
var sass = require( 'gulp-sass' );
var autoprefixer = require( 'gulp-autoprefixer' );
var del = require( 'del' );
var sourcemaps = require( 'gulp-sourcemaps' );
var livereload = require( 'gulp-livereload' );
var csscomb = require( 'gulp-csscomb' );

gulp.task( 'sass', function() {
  return gulp.src( './assets/scss/style.scss' )
    .pipe( sass( { style: 'expanded' } ).on( 'error', sass.logError ) )
    .pipe( autoprefixer( { browsers: ['last 2 versions', 'ie >= 9'], cascade: false } ) )
    .pipe( sourcemaps.write( './', { includeContent: false, sourceRoot: 'source' } ) )
    .pipe( csscomb() )
    .pipe( gulp.dest( './includes/public/css' ) )
    .pipe( livereload() );
});

gulp.task( 'clean', function( done ) {
  del( ['./includes/public/css/style.css'], done );
});

gulp.task( 'copy', function() {
  gulp.src( ['./node_modules/world-countries/dist/countries.json', './node_modules/world-countries/data/*.json'] )
    .pipe( gulp.dest( './includes/public/js/country_data' ) );
  gulp.src( ['./node_modules/world-countries/data/*.svg'] )
      .pipe( gulp.dest( './includes/public/svg' ) );
});

gulp.task( 'build', ['copy', 'clean', 'sass'] );

gulp.task( 'watch', function() {
  // Watch .scss files
  gulp.watch( './assets/scss/**/*.scss', ['sass'] );
});

// Default task is watch
gulp.task( 'default', ['build', 'watch'] )
