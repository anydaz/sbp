const mix = require('laravel-mix');
var path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .react()
    .postCss("resources/css/app.css", "public/css", [
     require("tailwindcss"),
    ])
    .webpackConfig({
        resolve: {
            symlinks: false,
            extensions: ['.js', '.json'],
            alias: {
            	components: path.resolve(__dirname, 'resources/js/components'),
                hooks: path.resolve(__dirname, 'resources/js/hooks'),
                root: path.resolve(__dirname, 'resources/js'),
                helper: path.resolve(__dirname, 'resources/js/helper'),
            }
        }
    })
