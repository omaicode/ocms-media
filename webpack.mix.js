const dotenvExpand = require('dotenv-expand');
dotenvExpand(require('dotenv').config({ path: '../../.env'/*, debug: true*/}));

const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public/modules/media').mergeManifest();

mix
.sass(__dirname + '/resources/assets/scss/media.scss', 'css/media.css')
.js(__dirname + '/resources/assets/js/media.js', 'js/media.js')
.vue();

if (mix.inProduction()) {
    mix.version();
}
