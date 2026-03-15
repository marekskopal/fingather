import { expect, test } from '@playwright/test';

import { DividendsPage } from './pages/dividends.page';

test.describe('Dividends page', () => {
    test('dividends page loads', async ({ page }) => {
        const dividends = new DividendsPage(page);
        await dividends.goto();
        await dividends.expectLoaded();
    });

    test('history tab is active by default and shows chart', async ({ page }) => {
        const dividends = new DividendsPage(page);
        await dividends.goto();
        await dividends.expectLoaded();
        await dividends.expectHistoryTabActive();
    });

    test('history tab shows transaction list', async ({ page }) => {
        const dividends = new DividendsPage(page);
        await dividends.goto();
        await dividends.expectLoaded();
        await dividends.expectTransactionListVisible();
    });

    test('switching to forecast tab shows calendar', async ({ page }) => {
        const dividends = new DividendsPage(page);
        await dividends.goto();
        await dividends.expectLoaded();
        await dividends.clickForecastTab();
        await dividends.expectForecastVisible();
    });
});
