import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Echo and Pusher
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Configure Laravel Echo with Pusher for Reverb (Reverb is Pusher compatible)
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'ringette-leagues-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    cluster: 'mt1',
});
