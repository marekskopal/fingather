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
});

test.describe('Settings - Benchmark assets', () => {
    test('benchmark assets page loads', async ({ page }) => {
        const settings = new SettingsPage(page);
        await settings.gotoBenchmarkAssets();
        await settings.expectBenchmarkAssetsLoaded();
    });

    test('benchmark assets page has ticker search selector', async ({ page }) => {
        await page.goto('/settings/benchmark-assets');
        await expect(page).toHaveURL(/\/settings\/benchmark-assets/, { timeout: 5000 });
        await expect(page.locator('fingather-ticker-search-selector')).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Settings - Import mappings', () => {
    test('import mappings page loads', async ({ page }) => {
        const settings = new SettingsPage(page);
        await settings.gotoImportMappings();
        await settings.expectImportMappingsLoaded();
    });

    test('import mappings page shows table', async ({ page }) => {
        await page.goto('/settings/import-mappings');
        await page.waitForSelector('.card', { timeout: 10000 });
        await expect(page.locator('table')).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Settings - API keys', () => {
    test('api keys page loads', async ({ page }) => {
        const settings = new SettingsPage(page);
        await settings.gotoApiKeys();
        await settings.expectApiKeysLoaded();
    });

    test('add api key form loads', async ({ page }) => {
        const settings = new SettingsPage(page);
        await settings.gotoAddApiKey();
        await settings.expectAddApiKeyFormLoaded();
    });

    test('add api key form has type and apiKey fields', async ({ page }) => {
        const settings = new SettingsPage(page);
        await settings.gotoAddApiKey();
        await expect(page.locator('fingather-select#type')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('input#apiKey')).toBeVisible();
    });

    test('cancel on add api key form returns to list', async ({ page }) => {
        await page.goto('/settings/api-keys/add-api-key');
        await page.waitForSelector('fingather-select#type', { timeout: 10000 });
        await page.locator('a.btn-secondary').click();
        await expect(page).toHaveURL(/\/settings\/api-keys$/, { timeout: 10000 });
    });

    test('create and delete api key', async ({ page }) => {
        const settings = new SettingsPage(page);
        await settings.gotoAddApiKey();

        await settings.selectFirstApiKeyType();
        await settings.fillApiKeyValue('test-api-key-e2e-placeholder');
        await settings.submitForm();
        await settings.expectRedirectedToApiKeyList();

        const rowCountBefore = await settings.getApiKeyRowCount();

        await settings.clickDeleteFirstApiKey();
        await settings.confirmDelete();

        await expect(page.locator('table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});
