import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/purchase-orders.js',
                'resources/js/inspection.js',
                'resources/js/inventory-assignment.js',
                'resources/js/bac-purchase-requests.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
