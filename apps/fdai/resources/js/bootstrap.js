window._ = require('lodash');
/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */
window.Popper = require('popper.js').default;
window.$ = window.jQuery = require('jquery');
require('bootstrap');

require('bootstrap-datetimepicker/src/js/bootstrap-datetimepicker');
require('datatables.net-bs4');
require('datatables.net-buttons-bs4');
require('moment');
// AdminLTE App
require('icheck');
require('select2');
//require('pace-js');
//require('adminlte');
require('bootstrap-sass');
//require('@material-ui/core');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.warn('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
import Echo from 'laravel-echo'
window.Pusher = require('pusher-js');
Pusher.logToConsole = true;
window.Echo = new Echo({
    broadcaster: 'pusher',
    //key: process.env.MIX_PUSHER_APP_KEY,
    //cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    key: '4e7cd12d82bff45e4976',
    cluster: 'us2',
    //encrypted: true
});
console.log("USER AGENT: "+navigator.userAgent);
// Just include this directly or we get duplicate errors
// import bugsnag from '@bugsnag/js'
// if(typeof bugsnag !== "undefined"){
//     window.bugsnagClient = bugsnag('3de50ea1404eb28810229d41cfe30603')
// }
// import LogRocket from 'logrocket';
// if(window.location.href.indexOf("app.quantimo.do") > -1 && LogRocket){
//     LogRocket.init('mkcthl/quantimodo');
// }

