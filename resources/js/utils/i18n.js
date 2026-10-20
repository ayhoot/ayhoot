import { createI18n } from 'vue-i18n';
import en from '../locales/en.json';
import fa from '../locales/fa.json';


function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

function getInitialLocale() {
    const savedLang = getCookie('lang');
    if (savedLang && ['en', 'fa'].includes(savedLang)) {
        return savedLang;
    }
    return 'en';
}

const i18n = createI18n({
    legacy: false,
    locale: getInitialLocale(),
    fallbackLocale: 'en',
    messages: {
        en,
        fa,
    },
});

export default i18n;
