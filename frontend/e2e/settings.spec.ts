import { expect, test } from '@playwright/test';

import { SettingsPage } from './pages/settings.page';

test.describe('Settings', () => {
    test('settings page loads', async ({ page }) => {
        const settings = new SettingsPage(page);
        await settings.goto();
        await settings.expectLoaded();
    });

    test('settings groups page is accessible', async ({ page }) => {
        const settings = new SettingsPage(page);
        await settings.gotoGroups();
        await settings.expectGroupsLoaded();
    });

    test('settings api-keys page is accessible', async ({ page }) => {
        await page.goto('/settings/api-keys');
        await expect(page).toHaveURL(/\/settings\/api-keys/, { timeout: 5000 });
    });
});
