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
        // Wait for spinner to disappear (data loaded)
        await page.waitForSelector('.spinner-border', { state: 'hidden', timeout: 10000 }).catch(() => {});
        // Either rows in the asset table exist, or the spinner is still gone (empty list)
        const hasRows = await page.locator('fingather-opened-assets').isVisible().catch(() => false);
        expect(hasRows).toBeTruthy();
    });
});
