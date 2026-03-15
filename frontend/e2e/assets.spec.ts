import { expect, test } from '@playwright/test';

import { AssetsPage } from './pages/assets.page';

test.describe('Assets', () => {
    test('assets page loads', async ({ page }) => {
        const assets = new AssetsPage(page);
        await assets.goto();
        await assets.expectLoaded();
    });

    test('assets page shows asset list or empty state', async ({ page }) => {
        await page.goto('/assets');
        // Wait for either the ungrouped or grouped asset list to appear
        await page.locator('fingather-opened-assets, fingather-opened-grouped-assets').first().waitFor({ state: 'visible', timeout: 15000 });
    });
});

test.describe('Asset detail', () => {
    test('clicking an asset navigates to its detail page', async ({ page }) => {
        const assets = new AssetsPage(page);
        await assets.goto();
        await assets.expectLoaded();

        if (!(await assets.hasAssets())) {
            test.skip(true, 'No assets available to navigate to detail');
            return;
        }

        await assets.clickFirstAsset();
        await assets.expectDetailVisible();
    });

    test('asset detail page shows asset value component', async ({ page }) => {
        const assets = new AssetsPage(page);
        await assets.goto();
        await assets.expectLoaded();

        if (!(await assets.hasAssets())) {
            test.skip(true, 'No assets available');
            return;
        }

        await assets.clickFirstAsset();
        await expect(page).toHaveURL(/\/assets\/\d+/, { timeout: 10000 });
        await expect(page.locator('fingather-asset-value')).toBeVisible({ timeout: 10000 });
    });

    test('asset detail back link returns to assets list', async ({ page }) => {
        const assets = new AssetsPage(page);
        await assets.goto();
        await assets.expectLoaded();

        if (!(await assets.hasAssets())) {
            test.skip(true, 'No assets available');
            return;
        }

        await assets.clickFirstAsset();
        await expect(page).toHaveURL(/\/assets\/\d+/, { timeout: 10000 });

        await page.locator('a.btn-link').click();
        await expect(page).toHaveURL('/assets', { timeout: 10000 });
    });
});
