import { expect, test } from '@playwright/test';

import { DashboardPage } from './pages/dashboard.page';

test.describe('Dashboard', () => {
    test('dashboard loads with portfolio total section', async ({ page }) => {
        const dashboard = new DashboardPage(page);
        await dashboard.goto();
        await dashboard.expectPortfolioSectionVisible();
    });

    test('dashboard shows group data section', async ({ page }) => {
        const dashboard = new DashboardPage(page);
        await dashboard.goto();
        await dashboard.expectGroupDataVisible();
    });

    test('navigation to assets works', async ({ page }) => {
        await page.goto('/');
        await page.goto('/assets');
        await expect(page).toHaveURL('/assets');
    });

    test('navigation to transactions works', async ({ page }) => {
        await page.goto('/');
        await page.goto('/transactions');
        await expect(page).toHaveURL('/transactions');
    });
});
