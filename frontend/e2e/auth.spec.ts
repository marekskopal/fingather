import { expect, test } from '@playwright/test';

import { LoginPage } from './pages/login.page';

// These tests run without stored auth state
test.use({ storageState: { cookies: [], origins: [] } });

test.describe('Authentication', () => {
    test('login success redirects to dashboard', async ({ page }) => {
        const login = new LoginPage(page);
        await login.goto();

        await login.login(
            process.env['E2E_USER_EMAIL'] ?? 'test@fingather.test',
            process.env['E2E_USER_PASSWORD'] ?? 'Test1234!',
        );

        await expect(page).toHaveURL('/', { timeout: 10000 });
    });

    test('login failure shows error', async ({ page }) => {
        const login = new LoginPage(page);
        await login.goto();

        await login.login('wrong@example.com', 'wrongpassword');
        await login.expectLoginError();
    });

    test('unauthenticated access redirects to login', async ({ page }) => {
        await page.goto('/');
        await expect(page).toHaveURL(/\/authentication\/login/, { timeout: 5000 });
    });

    test('unauthenticated access to assets redirects to login', async ({ page }) => {
        await page.goto('/assets');
        await expect(page).toHaveURL(/\/authentication\/login/, { timeout: 5000 });
    });
});
