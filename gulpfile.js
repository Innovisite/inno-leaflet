var gulp = require('gulp');
var mkdirp = require('mkdirp');
var jshint = require('gulp-jshint');
var duration = require('gulp-duration');
var less = require('gulp-less');
var LessPluginCleanCSS = require('less-plugin-clean-css');
var concat_css = require('gulp-concat-css');
var minify_css = require('gulp-cssnano');
var concat_js = require('gulp-concat');
var minify_js = require("gulp-uglify");
var cleancss = new LessPluginCleanCSS({ advanced: true });

var cssSources = [
    "node_modules/leaflet/dist/leaflet.css",
    ];

var localJSSources = [ 
    "lib/*.js"
];

var externalJSSources = [ 
    'node_modules/jquery/dist/jquery.js',
    'node_modules/leaflet/dist/leaflet.js'
];

var jsSources = externalJSSources.concat(localJSSources);

mkdirp('wwwroot/build');
mkdirp('wwwroot/build/fonts');
mkdirp('wwwroot/build/js');
mkdirp('wwwroot/build/css');
mkdirp('wwwroot/build/html');

gulp.task('build', [ 'common', 'prepare-css', 'prepare-js' ]);

gulp.task('common', [ 'lint' ]);

gulp.task('lint', function() {
    return gulp.src(localJSSources)
    .pipe(jshint())
    .pipe(jshint.reporter('default'))
    .pipe(jshint.reporter('fail'));
});

gulp.task('prepare-css', function() {
    return buildCSS(cssSources, false);
});

gulp.task('prepare-js', function() {
    return buildJS(jsSources, 'lib.min.js', 'wwwroot/build/js/', false);
});

function buildCSS(files, minify) {
    var g = gulp.src(files)
	.pipe(less({plugins: [cleancss]}))
	.pipe(concat_css('style.min.css',
			 { rebaseUrls: false }));
    if(minify) {
	g = g.pipe(minify_css({zindex: false}));
    }
    return g.pipe(duration('Execution Time: '))
	.pipe(gulp.dest('wwwroot/build/css/'));
}

function buildJS(files, destName, destDir, minify) {
    var g = gulp.src(files)
	.pipe(concat_js(destName)); 
    if(minify) {
	g = g.pipe(minify_js());
    }
    return g.pipe(duration('Execution Time: '))
	.pipe(gulp.dest(destDir));
}
