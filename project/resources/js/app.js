import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        console.log(`Resolving page component: ${name}`);
        try {
            const page = resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue'),
            );
            return page;
        } catch (error) {
            console.error(`Error resolving page component: ${name}`, error);
            throw error;
        }
    },
    setup({ el, App, props, plugin }) {
        return createApp({ 
            render: () => h(App, props),
            errorCaptured(err, instance, info) {
                console.error('Inertia Error:', err, info);
                return false;
            }
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
        showSpinner: true,
    },
});
