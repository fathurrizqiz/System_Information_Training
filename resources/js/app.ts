import axios from 'axios';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import Vue3Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';


// import 'vue-select/dist/vue-select.css';

window.axios = axios;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content')!;
}

createInertiaApp({
    title: (title) => title,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin).use(ZiggyVue).use(Vue3Toastify, {
            autoClose: 3000,
            position: 'top-right',
        });

        // Register vue-select globally
        // app.component('v-select', VueSelect as DefineComponent);

        app.mount(el);
    },
    progress: {
    color: '#06b6d4',       // ganti warna sesuai tema (contoh: cyan)
    delay: 250,             // delay sebelum muncul (ms), biar gak flicker untuk request cepat
    includeCSS: true,       // otomatis inject CSS bar-nya
    showSpinner: true,      // munculkan spinner kecil di pojok kanan atas
},
});

initializeTheme();
