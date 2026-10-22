const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 */

// 1. App ADMIN (panel principal)
mix.js('resources/js/app.js', 'public/js')
    .vue()  // ← ESTA LÍNEA ES IMPORTANTE
    .sass('resources/sass/app.scss', 'public/css');

// 2. App PÚBLICA (registrador)
mix.js('resources/js/public-app.js', 'public/js/public-app.js')
    .vue()  // ← ESTA LÍNEA ES IMPORTANTE
    .sass('resources/sass/public/registrador-app.scss', 'public/css/registrador-app.css')