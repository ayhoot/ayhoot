// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: false,
        }),
    ],

    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/js'),
        },
        mainFields: ['browser', 'module', 'main', 'jsnext:main', 'jsnext'],
    },

    optimizeDeps: {
        // Pre-bundle commonly-imported deps so Vite doesn't discover them at
        // runtime and trigger full-page reloads (the dev-mode "reload loop").
        include: [
            'vue',
            'vue-router',
            'vue-i18n',
            'axios',
            'chart.js',
            'vue-chartjs',
            'd3-shape',
            'dompurify',
            'vuedraggable',
            'qrcode',
            'qrcode.vue',
            'laravel-echo',
            'pusher-js',
            'canvas-confetti',
            '@vueup/vue-quill',
            'vue-toast-notification',
            'vue3-lottie',
        ],
    },

    server: {
        host: '127.0.0.1',
        port: 5173,
        // Don't watch Laravel's server-side dirs. Laravel's file cache/session
        // driver writes to storage/framework/* on every request; if Vite watches
        // those it issues a full page reload on each write -> infinite reload loop.
        watch: {
            ignored: [
                '**/storage/**',
                '**/bootstrap/cache/**',
                '**/vendor/**',
                '**/node_modules/**',
                '**/.git/**',
                '**/public/build/**',
                '**/tests/**',
                '**/*.log',
            ],
        },
        proxy: {
            // Proxy API calls to Laravel dev server
            '/api': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false,
            },
            // Proxy broadcasting auth endpoint so Echo can auth via the dev server
            '/broadcasting/auth': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false,
            },
            // Optional: proxy websockets path if you expose the websockets server via HTTP path
            '/laravel-websockets': {
                target: 'http://127.0.0.1:6001',
                ws: true,
                changeOrigin: true,
                secure: false,
            },
        },
    },

    build: {
        outDir: 'public/build',
        rollupOptions: {
            output: {
                assetFileNames: 'assets/[name].[hash][extname]',
                chunkFileNames: 'assets/[name].[hash].js',
                entryFileNames: 'assets/[name].[hash].js',
            },
        },
    },
});
