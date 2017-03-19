const { mix } = require('laravel-mix');

mix.js('resources/assets/js/app.js', 'public/js')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/','public/fonts/bootstrap')
    .sass('resources/assets/sass/app.scss', 'public/css');