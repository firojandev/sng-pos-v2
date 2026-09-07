import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    let host = undefined;

    try {
        if (env.APP_URL) {
            const parsed = new URL(env.APP_URL);
            if (parsed.hostname && parsed.hostname !== 'localhost' && parsed.hostname !== '127.0.0.1') {
                host = parsed.hostname;
            }
        }
    } catch {
        // fallback to default
    }

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
        server: {
            host: '0.0.0.0',
            ...(host ? { hmr: { host } } : {}),
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});

