import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Utilise window.location.hostname pour être compatible avec Docker et tous les environnements.
// Les variables VITE sont compilées au moment du build, mais l'hôte peut changer selon l'environnement.
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
const isTLS = reverbScheme === 'https';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: window.location.hostname,
    wsPort: isTLS ? 443 : 80,
    wssPort: 443,
    forceTLS: isTLS,
    enabledTransports: isTLS ? ['wss'] : ['ws'],
    authEndpoint: '/broadcasting/auth',
});
