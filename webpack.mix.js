const mix = require('laravel-mix');

mix.js('resources/assets/js/app.js', 'public/js')
    .vue({ version: 2 })
    .sass('resources/assets/sass/app.scss', 'public/css')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/', 'public/fonts/bootstrap')
    .version();