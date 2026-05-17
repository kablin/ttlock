import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import axios from 'axios';
import '@fontsource/inter/400.css'    // normal
import '@fontsource/inter/500.css'    // medium
import '@fontsource/inter/600.css'    // semi-bold
import '@fontsource/inter/700.css'    // bold

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';



axios.defaults.withCredentials = true;


axios.interceptors.response.use(
    (res) => res,
    async (err) => {
        const status = err.response?.status;
        const originalRequest = err.config;

        // 1. Флаг защиты от бесконечного цикла
        // Если запрос уже помечен как повторный, не обрабатываем его снова
        if (originalRequest._retry) {
            return Promise.reject(err);
        }

        // 2. Обрабатываем только нужные ошибки
        if (status === 401 || status === 419) {
            console.log('reload '+status)
            originalRequest._retry = true; // Помечаем, что этот запрос сейчас будем повторять

            try {
                // 3. Тихо обновляем сессию/токен
                await axios.get('/session/refresh');
                
                // 4. ПОВТОРНО ОТПРАВЛЯЕМ исходный запрос и ВОЗВРАЩАЕМ его результат
                // axios(originalRequest) автоматически подхватит новый CSRF-токен из куки
                return axios(originalRequest);
                
            } catch (refreshError) {
                // Если обновление сессии не удалось 
                return Promise.reject(refreshError);
            }
        }

        return Promise.reject(err);
    }
);




createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
