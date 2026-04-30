import { expect, test } from '@playwright/test';

import { ImpersonationBannerPage } from './pages/impersonation-banner.page';
import { LoginPage } from './pages/login.page';
import { UsersListPage } from './pages/users-list.page';

const ADMIN_EMAIL = process.env['E2E_USER_EMAIL'] ?? 'test@fingather.test';
const TARGET_EMAIL = 'target@fingather.test';
const TARGET_PASSWORD = 'Target1234!';
const API_BASE = (process.env['E2E_BASE_URL'] ?? 'http://localhost:4200') + '/api';

const readAccessToken = async (page: import('@playwright/test').Page): Promise<string> => {
    return await page.evaluate(() => {
        const raw = localStorage.getItem('authentication') ?? '{}';
        const auth = JSON.parse(raw) as { accessToken?: string };
        return auth.accessToken ?? '';
    });
};

test.describe('Admin impersonation', () => {
    test('admin can switch to a user and switch back', async ({ page }) => {
        const list = new UsersListPage(page);
        const banner = new ImpersonationBannerPage(page);

        await list.goto();
        await list.switchToFor(TARGET_EMAIL);

        await expect(page).toHaveURL('/', { timeout: 10000 });
        await banner.expectVisible(TARGET_EMAIL);

        await page.goto('/account');
        await expect(page.locator('table')).toContainText(TARGET_EMAIL, { timeout: 10000 });

        await banner.switchBack();
        await banner.expectHidden();

        await page.goto('/account');
        await expect(page.locator('table')).toContainText(ADMIN_EMAIL, { timeout: 10000 });
    });

    test('switch-to button is hidden for self and for admins', async ({ page }) => {
        const list = new UsersListPage(page);
        await list.goto();
        await list.expectNoSwitchToFor(ADMIN_EMAIL);
        await list.expectSwitchToFor(TARGET_EMAIL);
    });

    test('impersonation token cannot access admin endpoints', async ({ page, request }) => {
        const list = new UsersListPage(page);
        const banner = new ImpersonationBannerPage(page);

        await list.goto();
        await list.switchToFor(TARGET_EMAIL);
        await banner.expectVisible(TARGET_EMAIL);

        const token = await readAccessToken(page);
        const res = await request.get(`${API_BASE}/admin/user`, {
            headers: { Authorization: `Bearer ${token}` },
        });
        expect(res.status()).toBe(403);
    });

    test('impersonation token cannot delete current user or change password', async ({ page, request }) => {
        const list = new UsersListPage(page);
        const banner = new ImpersonationBannerPage(page);

        await list.goto();
        await list.switchToFor(TARGET_EMAIL);
        await banner.expectVisible(TARGET_EMAIL);

        const token = await readAccessToken(page);

        const del = await request.delete(`${API_BASE}/current-user`, {
            headers: { Authorization: `Bearer ${token}` },
        });
        expect(del.status()).toBe(403);

        const upd = await request.put(`${API_BASE}/current-user`, {
            headers: { Authorization: `Bearer ${token}` },
            data: { password: 'NewPass1234!', name: 'Hacked' },
        });
        expect(upd.status()).toBe(403);
    });

    test('impersonation refresh-token request is rejected', async ({ page, request }) => {
        const list = new UsersListPage(page);
        const banner = new ImpersonationBannerPage(page);

        await list.goto();
        await list.switchToFor(TARGET_EMAIL);
        await banner.expectVisible(TARGET_EMAIL);

        const access = await readAccessToken(page);
        const res = await request.post(`${API_BASE}/authentication/refresh-token`, {
            headers: { Authorization: `Bearer ${access}` },
            data: { refreshToken: access },
        });
        expect(res.status()).toBe(401);
    });

    test('non-admin user cannot impersonate', async ({ browser, request }) => {
        const ctx = await browser.newContext({ storageState: { cookies: [], origins: [] } });
        const page = await ctx.newPage();
        const login = new LoginPage(page);
        await login.goto();
        await login.login(TARGET_EMAIL, TARGET_PASSWORD);
        await expect(page).toHaveURL('/', { timeout: 10000 });

        const access = await readAccessToken(page);
        const res = await request.post(`${API_BASE}/admin/user/1/impersonate`, {
            headers: { Authorization: `Bearer ${access}` },
            data: {},
        });
        expect(res.status()).toBe(401);

        await ctx.close();
    });

    test('admin cannot impersonate self or other admins', async ({ page, request }) => {
        const access = await readAccessToken(page);
        const ownId = await page.evaluate(() => {
            const raw = localStorage.getItem('authentication') ?? '{}';
            const auth = JSON.parse(raw) as { userId?: number };
            return auth.userId ?? 0;
        });

        const res = await request.post(`${API_BASE}/admin/user/${ownId}/impersonate`, {
            headers: { Authorization: `Bearer ${access}` },
            data: {},
        });
        expect(res.status()).toBe(403);
    });

    test('expired impersonation token forces switch back', async ({ page }) => {
        // docker-compose.test.yml sets IMPERSONATION_TOKEN_EXPIRATION=5
        const list = new UsersListPage(page);
        const banner = new ImpersonationBannerPage(page);

        await list.goto();
        await list.switchToFor(TARGET_EMAIL);
        await banner.expectVisible(TARGET_EMAIL);

        await page.waitForTimeout(7000);

        await page.goto('/account');
        await banner.expectHidden();
        await expect(page.locator('table')).toContainText(ADMIN_EMAIL, { timeout: 10000 });
    });
});
