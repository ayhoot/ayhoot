/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Add a request interceptor to always include the latest token
window.axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

// Add a response interceptor to handle 401s similarly to apiService
window.axios.interceptors.response.use(response => response, error => {
    if (error.response?.status === 401) {
        console.warn('[Axios] 401 detected, clearing session.');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('role');
    }
    return Promise.reject(error);
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const normalizeEnvString = (value) => {
    if (value === undefined || value === null) return '';
    const normalized = String(value).trim();
    if (!normalized || normalized === 'undefined' || normalized === 'null') return '';
    return normalized;
};

const toPositiveInt = (value, fallback) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
};

const isLocalHost = (host) => ['localhost', '127.0.0.1', '::1'].includes(host);

// Centralized WebSocket configuration
export const getWebSocketConfig = () => {
    const appConfig = typeof window !== 'undefined' ? (window.__APP_CONFIG__ || {}) : {};
    const browserHost = typeof window !== 'undefined' ? window.location.hostname : '127.0.0.1';
    const isHttps = typeof window !== 'undefined' && window.location.protocol === 'https:';
    const isBrowserLocal = isLocalHost(browserHost);

    // Host determination:
    // When accessing over a public domain / IP, we MUST connect to that same origin,
    // NEVER to 127.0.0.1 or localhost (which would attempt to connect to the client device).
    let wsHost = appConfig.PUSHER_CLIENT_HOST || '';
    if (!wsHost && !import.meta.env.PROD) {
        wsHost = normalizeEnvString(import.meta.env.VITE_PUSHER_HOST);
    }
    if (!wsHost || (!isBrowserLocal && isLocalHost(wsHost))) {
        wsHost = browserHost;
    }

    const cluster = appConfig.PUSHER_APP_CLUSTER || normalizeEnvString(import.meta.env.VITE_PUSHER_APP_CLUSTER) || 'mt1';
    const useCloudPusher = false;

    // TLS & Port determination:
    // If served over HTTPS, the browser MUST connect via WSS on 443 (proxied by Nginx).
    // Plain ws:// on an https:// site is blocked by modern browsers (Mixed Content).
    const forceTLS = isHttps || (!isBrowserLocal && !import.meta.env.DEV);
    const scheme = forceTLS ? 'https' : 'http';

    const rawPort = appConfig.PUSHER_PORT || import.meta.env.VITE_PUSHER_PORT;
    const wsPort = forceTLS ? 443 : toPositiveInt(rawPort, 6001);
    const appKey = appConfig.PUSHER_APP_KEY || normalizeEnvString(import.meta.env.VITE_PUSHER_APP_KEY) || '5c8a81ec227bf87005fe';
    const wsPath = normalizeEnvString(import.meta.env.VITE_PUSHER_PATH);

    const config = {
        broadcaster: 'pusher',
        key: appKey,
        disableStats: true,
        authEndpoint: '/broadcasting/auth',
    };

    if (useCloudPusher) {
        config.cluster = cluster;
        config.forceTLS = true;
        config.enabledTransports = ['wss'];
    } else {
        config.cluster = cluster;
        config.wsHost = wsHost;
        config.wsPort = wsPort;
        config.wssPort = wsPort;
        config.forceTLS = forceTLS;
        
        // pusher-js uses the transport name 'ws' for BOTH plain and TLS sockets —
        // `forceTLS` decides which. There is NO 'wss' transport name, so setting
        // enabledTransports:['wss'] leaves no usable transport and the socket never
        // opens (silent "Connecting…" forever on https). Always enable 'ws';
        // forceTLS transparently upgrades it to wss when the page is https.
        config.enabledTransports = ['ws'];

        if (wsPath) {
            config.wsPath = wsPath.startsWith('/') ? wsPath : `/${wsPath}`;
        }
    }

    // Add auth header for authenticated channels
    const token = typeof localStorage !== 'undefined' ? localStorage.getItem('token') : null;
    if (token) {
        config.auth = {
            headers: {
                Authorization: `Bearer ${token}`
            }
        };
    }

    console.log('[WebSocket] Configured:', {
        mode: useCloudPusher ? 'cloud' : 'self-hosted',
        host: config.wsHost || '(pusher cloud)',
        port: config.wsPort || '(default)',
        forceTLS: config.forceTLS,
        scheme: scheme,
        transports: config.enabledTransports
    });

    return config;
};


// Create a global Echo instance
try {
    window.Echo = new Echo(getWebSocketConfig());
    console.log('[WebSocket] Echo initialized successfully');

    // Listen for connection errors to avoid silent failures
    if (window.Pusher) {
        window.Pusher.logToConsole = import.meta.env.VITE_DEBUG === 'true';
    }
} catch (error) {
    console.error('[WebSocket] Failed to initialize Echo:', error);
}

