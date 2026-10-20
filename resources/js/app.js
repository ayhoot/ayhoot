// resources/js/app.js
import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import i18n from "./utils/i18n";
import ToastPlugin from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-bootstrap.css';
import '../css/app.css';
import './bootstrap'; // Ensure bootstrap (Echo, Axios) is loaded
import ConfigPlugin from './plugins/config'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

// DEBUG: Track reloads and prevent infinite loops
(function() {
    const RELOAD_KEY = 'app_reload_count';
    const TIME_KEY = 'app_reload_time';
    const now = Date.now();
    let count = parseInt(sessionStorage.getItem(RELOAD_KEY) || '0');
    let firstReloadTime = parseInt(sessionStorage.getItem(TIME_KEY) || now.toString());

    if (now - firstReloadTime > 10000) {
        // Reset after 10 seconds
        count = 1;
        firstReloadTime = now;
    } else {
        count++;
    }

    sessionStorage.setItem(RELOAD_KEY, count.toString());
    sessionStorage.setItem(TIME_KEY, firstReloadTime.toString());

    if (count > 5) {
        const msg = '!!! CRITICAL: Infinite reload loop detected. Halting application to prevent crash. Check console for trace. !!!';
        console.error(msg);
        document.body.innerHTML = `<div style="background:red;color:white;padding:20px;font-family:sans-serif;text-align:center;">
            <h1>${msg}</h1>
            <p>Please check your network and try again. If the problem persists, contact support.</p>
            <button onclick="sessionStorage.clear(); location.reload();" style="padding:10px;cursor:pointer;">Reset and Try Again</button>
        </div>`;
        window.stop();
        throw new Error(msg);
    }

    window.addEventListener('beforeunload', () => {
        console.warn('[App] Page is reloading...');
    });
})();

const trimTrailingSlash = (value = '') => String(value).replace(/\/+$/, '')
const ensureApiRoot = (value = '') => {
    const normalized = trimTrailingSlash(value)
    return normalized.endsWith('/api') ? normalized : `${normalized}/api`
}

const baseUrl = trimTrailingSlash(import.meta.env.VITE_BASE_URL || window.location.origin)
const apiRoot = ensureApiRoot(import.meta.env.VITE_API_URL || baseUrl)

const app = createApp(App);
window.app = app; // Expose app for services (e.g., contentOwnerService, paymentService)
app.component('QuillEditor', QuillEditor)
app.use(router);
app.use(ConfigPlugin)
app.use(i18n); 
app.use(ToastPlugin);

app.provide('apiBaseURL', `${apiRoot}/`);
app.provide('imageBaseURL', `${baseUrl}/uploads/`);

// Attach router to the app instance so services can use window.app.$router
app.$router = router;

// Global property for role access in templates
Object.defineProperty(app.config.globalProperties, '$role', {
    get() {
        return localStorage.getItem('role') || 'teacher';
    }
});

app.mount("#app");
