import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: Echo<'reverb'>;
  }
}

// Make Pusher available globally for Laravel Echo
window.Pusher = Pusher;

const echoKey = import.meta.env.VITE_REVERB_APP_KEY || 'voyywzz0bsebqwbbcj3j';
const echoHost = import.meta.env.VITE_REVERB_HOST || (typeof window !== 'undefined' ? window.location.hostname : 'localhost');
const echoPort = Number(import.meta.env.VITE_REVERB_PORT || 8080);
const echoScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

export const echo = new Echo({
  broadcaster: 'reverb',
  key: echoKey,
  wsHost: echoHost,
  wsPort: echoPort,
  wssPort: echoPort,
  forceTLS: echoScheme === 'https',
  enabledTransports: ['ws', 'wss'],
});

if (typeof window !== 'undefined') {
  window.Echo = echo;
}

export default echo;
