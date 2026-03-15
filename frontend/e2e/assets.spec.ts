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
        // Either grouped or ungrouped asset list is shown
        const hasRows = await Promise.any([
            page.locator('fingather-opened-assets').isVisible(),
            page.locator('fingather-opened-grouped-assets').isVisible(),
        ]).catch(() => false);
        expect(hasRows).toBeTruthy();
    });
});
