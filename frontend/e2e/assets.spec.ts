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
