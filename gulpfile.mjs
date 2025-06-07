import { src, dest, watch, series, parallel } from "gulp";
import sassCompiler from "gulp-sass";
import * as sassPkg from "sass";
import terser from "gulp-terser";
import rename from "gulp-rename";
import sourcemaps from "gulp-sourcemaps";
import autoprefixer from "gulp-autoprefixer";

const sass = sassCompiler(sassPkg);

const paths = {
  css: {
    src: "assets/scss/**/*.scss",
    dest: "assets/css/",
  },
  js: {
    src: ["assets/js/*.js", "!assets/js/*.min.js", "!assets/js/*.map"], // Prevents an infinite loop.
    dest: "assets/js/",
  },
  maps: "/", // To save the maps files in the same folder as the original files.
};

/**
 * Compiles SCSS files to CSS.
 *
 * @returns {Stream} A Gulp stream that completes the SCSS compilation process.
 */
function compileSCSS() {
  return src(paths.css.src)
    .pipe(sourcemaps.init())
    .pipe(sass({ style: "compressed" }).on("error", sass.logError))
    .pipe(autoprefixer())
    .pipe(rename({ suffix: ".min" }))
    .pipe(sourcemaps.write(paths.maps))
    .pipe(dest(paths.css.dest));
}

/**
 * Compresses JavaScript files.
 *
 * @returns {Stream} A stream containing the processed JavaScript files.
 */
function compressJS() {
  return src(paths.js.src)
    .pipe(sourcemaps.init())
    .pipe(terser())
    .pipe(rename({ suffix: ".min" }))
    .pipe(sourcemaps.write(paths.maps))
    .pipe(dest(paths.js.dest));
}

/**
 * Watches for changes in CSS and JS source files.
 */
function watchFiles() {
  watch(paths.css.src, compileSCSS);
  watch(paths.js.src, compressJS);
}

export const build = parallel(compileSCSS, compressJS);
export default series(parallel(compileSCSS, compressJS), watchFiles);
