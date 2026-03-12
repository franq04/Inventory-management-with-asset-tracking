import $ from 'jquery';
import axios from 'axios';

window.$ = $;
window.jQuery = $;
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
