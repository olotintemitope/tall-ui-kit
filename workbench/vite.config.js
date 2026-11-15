import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath } from 'node:url';

/*
 * Builds the workbench gallery assets (both themes plus the kit JS) into
 * workbench/dist with stable names; workbench/routes/web.php serves them.
 */
const here = (path) => fileURLToPath(new URL(path, import.meta.url));

export default defineConfig({
    plugins: [tailwindcss()],
    build: {
        outDir: here('./dist'),
        emptyOutDir: true,
        cssCodeSplit: true,
        rollupOptions: {
            input: {
                neutral: here('./resources/css/neutral.css'),
                newbanger: here('./resources/css/newbanger.css'),
                gallery: here('./resources/js/gallery.js'),
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name][extname]',
            },
        },
    },
});
