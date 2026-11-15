import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/*
| Laravel Echo (лабораторная работа №11).
|
| pusher-js на клиенте, ключи приходят из .env через Vite (VITE_PUSHER_*).
| С облаком Pusher wsHost не задаётся — Echo сам возьмёт ws-<cluster>.pusher.com.
| С локальным сервером вещания (Reverb) wsHost/wsPort указывают на 127.0.0.1:8080.
*/

window.Pusher = Pusher;

const cluster = import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'eu';
const host = import.meta.env.VITE_PUSHER_HOST;
const port = import.meta.env.VITE_PUSHER_PORT ?? 443;
const forceTLS = (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster,
    wsHost: host || `ws-${cluster}.pusher.com`,
    wsPort: forceTLS ? 80 : Number(port),
    wssPort: forceTLS ? 443 : Number(port),
    forceTLS,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});
