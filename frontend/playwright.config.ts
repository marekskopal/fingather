import { defineConfig, devices } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

// Load .env.test from repo root when present
const envTestPath = path.resolve(__dirname, '../.env.test');
if (fs.existsSync(envTestPath)) {
    const lines = fs.readFileSync(envTestPath, 'utf-8').split('\n');
    for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) continue;
        const eqIdx = trimmed.indexOf('=');
        if (eqIdx === -1) continue;
        const key = trimmed.slice(0, eqIdx).trim();
        const value = trimmed.slice(eqIdx + 1).trim();
        if (!(key in process.env)) {
            process.env[key] = value;
        }
    }
}

export default defineConfig({
    testDir: './e2e',
    fullyParallel: true,
    forbidOnly: !!process.env['CI'],
    retries: process.env['CI'] ? 1 : 0,
    workers: process.env['CI'] ? 1 : undefined,
    reporter: process.env['CI'] ? 'github' : 'list',

    use: {
        baseURL: process.env['E2E_BASE_URL'] ?? 'https://localhost:4200',
        ignoreHTTPSErrors: true,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'on-first-retry',
    },

    webServer: {
        command: 'pnpm run start:e2e',
        url: process.env['E2E_BASE_URL'] ?? 'https://localhost:4200',
        reuseExistingServer: true,
        ignoreHTTPSErrors: true,
        timeout: 120000,
    },

    projects: [
        // Auth setup project — runs first, writes e2e/.auth/user.json
        {
            name: 'setup',
            testMatch: /setup\/.*\.setup\.ts/,
        },

        // Main test project — depends on auth setup, loads saved auth state
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
                storageState: 'e2e/.auth/user.json',
            },
            dependencies: ['setup'],
        },
    ],
});
