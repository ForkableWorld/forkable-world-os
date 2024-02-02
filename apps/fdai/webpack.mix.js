const mix = require('laravel-mix');

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

mix
    //.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');
// mix.styles([
//     "//cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css",
//     "https://adminlte.io/themes/AdminLTE/dist/css/skins/_all-skins.min.css",
//     "https://adminlte.io/themes/AdminLTE/plugins/pace/pace.min.css",
//     "https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/css/autoComplete.min.css",
//     "https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.3/css/AdminLTE.min.css",
//     "https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.3/css/skins/_all-skins.min.css",
//     "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.min.css",
//     "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css",
//     "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css",
//     "https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/square/_all.css",
//     "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css",
//     "https://code.getmdl.io/1.3.0/material.indigo-pink.min.css",
//     "https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css",
//     "https://fonts.googleapis.com/icon?family=Material+Icons",
//     "https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css",
//     "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css",
//     "https://unpkg.com/material-components-web@v4.0.0/dist/material-components-web.min.css",
//     "https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css",
//     "public/css/material-card.css?v=2.1.1",
//     "public/css/statistics-table.css",
//     "public/css/wp-button.css",
//     "public/css/medium-study.css",
//     "public/css/modern-AdminLTE.min.css",
// ], 'public/css/all.css');

mix.browserSync('local.quantimo.do');

mix.js('resources/js/app.js', 'public/js').vue({ version: 3 });
