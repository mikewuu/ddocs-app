/**
 * Loooooooooooooodash
 */
window._ = require('lodash');

/**
 * Handling cookies using JS
 */

window.Cookies = require('js-cookie');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

window.$ = window.jQuery = require('jquery');
require('bootstrap-sass');

/**
 * Vue is a modern JavaScript library for building interactive web interfaces
 * using reactive data binding and reusable components. Vue's API is clean
 * and simple, leaving you to focus on building your next great project.
 */

window.Vue = require('vue/dist/vue.js');
require('vue-resource');

/**
 * Vuex
 */

const Vuex = require('vuex');
window.store = new Vuex.Store(require('./store.js'));

/**
 * Vue Router
 */

window.router = require('./router.js');

/**
 * Auth Helper - Contains properties and methods for handling
 * front-end authentication.
 */

window.auth = require('./auth.js');
auth.setup();

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from "laravel-echo"

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: 'your-pusher-key'
// });


// Initialize autosize() for textareas.
let autosize = require('autosize');
$(document).ready(() => autosize($('textarea')));

// Define moment.js
window.moment = require('moment');

// tooltip.js (boostrap)
$(document).ready(() => $('[data-toggle="tooltip"]').tooltip());

// Include jQuery UI Datepicker
require('jquery-ui/ui/widgets/datepicker');

// Vue Filters
require('./filters.js');

// Vue Directives
require('./directives.js');

// Vue Animations
require('./animations.js');

// JS Helpers (utility functions)
require('./helpers.js');

// Page Fixed/Scroll Height adjustments
window.ResizeSensor = require('css-element-queries/src/ResizeSensor');

// Scroll monitor
window.scrollMonitor = require("scrollmonitor");

// Dragula
window.dragula = require('dragula');
window.autoScroll = require('dom-autoscroller');
