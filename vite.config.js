import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const resolveBasePath = (appUrl) => {
    if (!appUrl) {
        return '/build/';
    }

    try {
        const pathname = new URL(appUrl).pathname || '/';
        const trimmed = pathname.replace(/\/+$/, '');

        if (!trimmed || trimmed === '/') {
            return '/build/';
        }

        return `${trimmed}/build/`;
    } catch {
        return '/build/';
    }
};

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const basePath = resolveBasePath(env.APP_URL);

    return {
        base: basePath,
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/dashboard.js',
                    'resources/js/login.js',
                    'resources/js/purchase-orders.js',
                    'resources/js/purchase-requests-shared.js',
                    'resources/js/employee-purchase-requests.js',
                    'resources/js/custodian-purchase-requests.js',
                    'resources/js/inspection.js',
                    'resources/js/inventory-assignment.js',
                    'resources/js/bac-purchase-requests.js',
                ],
                refresh: true,
            }),
            tailwindcss(),
        ],
    };
});
