
/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

import $ from 'jquery';
import Vue from 'vue';
import moment from 'moment';
import axios from 'axios';

// Set jQuery on window first
window.$ = window.jQuery = $;

// Now import Bootstrap after jQuery is available
// Use a more explicit approach to ensure jQuery is available
const bootstrap = require('bootstrap-sass');

window.Vue = Vue;
window.moment = moment;
window.axios = axios;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Events = new Vue({});

/**
 * We'll register a HTTP interceptor to attach the "CSRF" header to each of
 * the outgoing requests issued by this application. The CSRF middleware
 * included with Laravel will automatically verify the header's value.
 */

/*Vue.http.interceptors.push((request, next) => {
    request.headers.set('X-CSRF-TOKEN', Laravel.csrfToken);

    next();
});*/

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
