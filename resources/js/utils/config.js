export default {
    appName: import.meta.env.VITE_APP_NAME || 'Ayhoot',
    apiUrl: import.meta.env.VITE_API_URL || (typeof window !== 'undefined' ? window.location.origin + '/api' : '/api'),
    version: import.meta.env.VITE_APP_VERSION || '1.0.0',
    debug: import.meta.env.VITE_DEBUG === 'true',
    baseUrl: import.meta.env.VITE_BASE_URL || window.location.origin,
}