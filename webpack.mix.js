const { mix } = require('laravel-mix');

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

let options = {
    processCssUrls: false
}

mix
    .sass('resources/assets/sass/main.scss',                    'public/assets/css')
    .sass('resources/assets/sass/bulma.scss',                   'public/assets/css')
    .sass('node_modules/font-awesome/scss/font-awesome.scss',   'public/assets/css')

    .js('resources/assets/js/app.js',                           'public/assets/js')

    .copy('resources/assets/img',                               'public/assets/img')
    .copy('resources/assets/icon',                              'public/assets/icon')
    .copy('node_modules/font-awesome/fonts',                    'public/assets/fonts')

    .extract(['vue', 'buefy', 'axios', 'form-serialize', 'qs', 'timeago.js'])
    
    .disableNotifications()
    .options(options)

if (mix.config.inProduction) {
    mix.version()
}
