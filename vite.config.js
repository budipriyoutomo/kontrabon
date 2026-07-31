import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Dipakai form publik maupun admin, jadi berdiri sendiri.
                'resources/js/perusahaan-select.js',
            ],
            refresh: true,
        }),
    ],
});
