<!-- src/App.vue -->
<template>
    <div v-if="error" class="error-boundary">
        <h2 class="text-xl font-bold text-red-600 mb-2">Something went wrong</h2>
        <p class="text-gray-600 mb-4">{{ error.message || 'An unexpected error occurred.' }}</p>
        <button @click="resetError" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
            Try Again
        </button>
    </div>
    <template v-else>
        <router-view />
        <ConfirmDialog />
    </template>
</template>

<script>
import { useI18n } from 'vue-i18n'
import { watchEffect, watch, ref, onErrorCaptured, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import ConfirmDialog from './components/shared/ConfirmDialog.vue'
import { notificationsRealtime } from './services/notificationsService'



export default {
    components: { ConfirmDialog },
    setup() {
        const { locale } = useI18n()
        const route = useRoute()
        const error = ref(null)

        // Global error handler for the window
        window.onerror = function(message, source, lineno, colno, err) {
            console.error('[Global Error]', message, err);
            // We only show the error UI for really critical stuff that breaks the mount
            if (message.includes('mount') || message.includes('render')) {
                error.value = err || new Error(message);
            }
        };

        onErrorCaptured((err) => {
            console.error('[Captured Error]', err);
            error.value = err;
            return false; // Stop propagation
        });

        const resetError = () => {
            error.value = null;
            window.location.reload();
        };

        // ... existing watchers ...
        watchEffect(() => {
            const isRtl = locale.value === 'fa'
            document.documentElement.setAttribute('lang', locale.value)
            document.documentElement.setAttribute('dir', isRtl ? 'rtl' : 'ltr')
        })

        watch(
            () => route.meta,
            (newMeta) => {
                if (newMeta && newMeta.showScrollbar === true) {
                    document.body.classList.add('scrollbar-visible')
                } else {
                    document.body.classList.remove('scrollbar-visible')
                }
            },
            { immediate: true }
        )

        // Real-time notifications for the logged-in user, across every panel.
        // connect() is idempotent and re-evaluates on each navigation so it picks
        // up login and tears down on logout.
        onMounted(() => notificationsRealtime.connect())
        watch(() => route.fullPath, () => notificationsRealtime.connect())

        return { error, resetError }
    },
}
</script>

<style>
.error-boundary {
    padding: 2rem;
    text-align: center;
    background: #fef2f2;
    border: 1px solid #fee2e2;
    border-radius: 0.5rem;
    margin: 2rem;
}
</style>
