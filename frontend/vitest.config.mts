import angular from '@analogjs/vite-plugin-angular';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [
        angular({ tsconfig: './tsconfig.json' }),
    ],
    resolve: {
        tsconfigPaths: true,
    },
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['src/test-setup.ts'],
        include: ['src/**/*.spec.ts'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'html'],
            include: ['src/app/**/*.ts'],
            exclude: ['src/app/**/*.spec.ts'],
        },
    },
});
