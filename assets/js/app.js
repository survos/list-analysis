// window.$ = window.jQuery = require('jquery');

/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');
global.$ = $;
window.jQuery = $;

require('bootstrap');
require("bootstrap/dist/css/bootstrap.css");

require('jquery-ui');
require('jqrangeslider');
require('@fortawesome/fontawesome-free');
import 'font-awesome/css/font-awesome.css';

import { library, dom } from "@fortawesome/fontawesome-svg-core";
dom.watch();

try {
    // for Node.js
    var autobahn = require('autobahn');
} catch (e) {
    // for browsers (where AutobahnJS is available globally)
}


