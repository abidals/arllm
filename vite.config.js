import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/site-nav.js', 'resources/js/ai-admin.js', 'resources/js/admin-cats.js', 'resources/js/media-replace.js'],
            refresh: true,
        }),
    ],
});
