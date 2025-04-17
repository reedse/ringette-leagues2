import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ mode }) => {
    // Load env file based on `mode` in the current working directory
    const env = loadEnv(mode, process.cwd(), '');
    
    return {
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, './resources/js'),
                'ziggy-js': path.resolve(__dirname, './vendor/tightenco/ziggy')
            },
        },
        define: {
            // Make env variables available to the client
            'import.meta.env.VITE_STRIPE_KEY': JSON.stringify(env.VITE_STRIPE_KEY),
            'import.meta.env.VITE_MONTHLY_PRICE_ID': JSON.stringify(env.VITE_MONTHLY_PRICE_ID),
            'import.meta.env.VITE_YEARLY_PRICE_ID': JSON.stringify(env.VITE_YEARLY_PRICE_ID),
        }
    };
});
