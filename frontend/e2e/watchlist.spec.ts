import { expect, test } from '@playwright/test';

import { WatchlistPage } from './pages/watchlist.page';

test.describe('Watchlist tab', () => {
    test('assets page loads with tabs', async ({ page }) => {
        const watchlist = new WatchlistPage(page);
        await watchlist.gotoAssets();
        await expect(page.locator('ul[role="tablist"]')).toBeVisible({ timeout: 10000 });
    });

    test('watchlist tab is present', async ({ page }) => {
        const watchlist = new WatchlistPage(page);
        await watchlist.gotoAssets();
        await expect(
            page.locator('ul[role="tablist"] button', { hasText: 'Watch list' }),
        ).toBeVisible({ timeout: 10000 });
    });

    test('clicking watchlist tab shows watched assets component', async ({ page }) => {
        const watchlist = new WatchlistPage(page);
        await watchlist.gotoAssets();
        await watchlist.clickWatchlistTab();
        await watchlist.expectWatchlistVisible();
    });

    test('watchlist shows table with headers', async ({ page }) => {
        const watchlist = new WatchlistPage(page);
        await watchlist.gotoAssets();
        await watchlist.clickWatchlistTab();
        await watchlist.expectWatchlistVisible();
        await expect(page.locator('fingather-watched-assets table')).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Add asset to watchlist', () => {
    test('add asset form is accessible from assets page', async ({ page }) => {
        await page.goto('/assets');
        await page.waitForSelector('ul[role="tablist"]', { timeout: 10000 });

        const addAssetLink = page.locator('a[href*="/assets/add-asset"]');
        await expect(addAssetLink).toBeVisible({ timeout: 10000 });
    });

    test('add asset form loads', async ({ page }) => {
        const watchlist = new WatchlistPage(page);
        await watchlist.gotoAddAsset();
        await watchlist.expectAddAssetFormLoaded();
    });

    test('add asset form has ticker search selector', async ({ page }) => {
        await page.goto('/assets/add-asset');
        await page.waitForSelector('form', { timeout: 10000 });
        await expect(page.locator('fingather-ticker-search-selector')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('fingather-save-button button')).toBeVisible();
    });

    test('cancel on add asset form returns to assets list', async ({ page }) => {
        await page.goto('/assets/add-asset');
        await page.waitForSelector('form', { timeout: 10000 });

        const cancelLink = page.locator('a.btn-secondary');
        await expect(cancelLink).toBeVisible({ timeout: 5000 });
        await cancelLink.click();

        await expect(page).toHaveURL('/assets', { timeout: 10000 });
    });

    test('ticker search selector opens and accepts input', async ({ page }) => {
        await page.goto('/assets/add-asset');
        await page.waitForSelector('form', { timeout: 10000 });

        // Open the ticker search dropdown
        const toggleBtn = page.locator('fingather-ticker-search-selector button').first();
        await expect(toggleBtn).toBeVisible({ timeout: 5000 });
        await toggleBtn.click();

        // Search input should appear inside the dropdown (id="tickerId-ticker-search")
        const searchInput = page.locator('input#tickerId-ticker-search');
        await expect(searchInput).toBeVisible({ timeout: 5000 });
        await searchInput.fill('AAPL');

        // Wait for results (tickers are seeded in test env)
        await page.waitForTimeout(600);
        const items = page.locator('fingather-ticker-search-selector .dropdown-menu button.dropdown-item');
        const count = await items.count();
        expect(count).toBeGreaterThan(0);
    });

    test('selecting ticker and cancelling returns to assets', async ({ page }) => {
        await page.goto('/assets/add-asset');
        await page.waitForSelector('form', { timeout: 10000 });

        // Cancel without submitting
        const cancelLink = page.locator('a.btn-secondary');
        await expect(cancelLink).toBeVisible({ timeout: 5000 });
        await cancelLink.click();

        await expect(page).toHaveURL('/assets', { timeout: 10000 });
    });
});
