import { expect, test } from '@playwright/test';

import { HistoryPage } from './pages/history.page';

test.describe('History page', () => {
    test('history page loads', async ({ page }) => {
        const history = new HistoryPage(page);
        await history.goto();
        await history.expectLoaded();
    });

    test('portfolio value chart is visible', async ({ page }) => {
        const history = new HistoryPage(page);
        await history.goto();
        await history.expectChartVisible();
    });

    test('time range tabs are visible', async ({ page }) => {
        const history = new HistoryPage(page);
        await history.goto();
        await history.expectLoaded();
        await history.expectRangeTabsVisible();
    });

    test('switching range tab stays on history page', async ({ page }) => {
        await page.goto('/history');
        await page.waitForSelector('ul.nav.nav-tabs', { timeout: 10000 });

        // Click the second range tab
        const tabs = page.locator('ul.nav.nav-tabs .nav-link');
        const tabCount = await tabs.count();
        if (tabCount > 1) {
            await tabs.nth(1).click();
        }

        await expect(page).toHaveURL('/history');
        await expect(page.locator('fingather-portfolio-value-chart')).toBeVisible({ timeout: 10000 });
    });
});
