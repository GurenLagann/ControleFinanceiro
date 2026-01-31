import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        // Minificacao de JS
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
            },
        },
        // Otimizacao de CSS
        cssMinify: true,
        // Gerar source maps apenas em dev
        sourcemap: false,
        // Chunk splitting para melhor cache
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['bootstrap'],
                },
                // Nomes de arquivos com hash para cache busting
                entryFileNames: 'js/[name]-[hash].js',
                chunkFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    const ext = assetInfo.name.split('.').pop();
                    if (/css/i.test(ext)) {
                        return 'css/[name]-[hash][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
        // Limite de tamanho de chunk (em KB)
        chunkSizeWarningLimit: 500,
    },
});
