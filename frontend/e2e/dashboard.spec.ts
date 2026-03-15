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

test.describe('Navbar navigation', () => {
    const navRoutes = [
        { label: 'assets', path: '/assets', expectedUrl: '/assets' },
        { label: 'transactions', path: '/transactions', expectedUrl: '/transactions' },
        { label: 'dividends', path: '/dividends', expectedUrl: '/dividends' },
        { label: 'overviews', path: '/overviews', expectedUrl: '/overviews' },
        { label: 'history', path: '/history', expectedUrl: '/history' },
        { label: 'strategies', path: '/strategies', expectedUrl: '/strategies' },
        { label: 'portfolios', path: '/portfolios', expectedUrl: '/portfolios' },
        { label: 'goals', path: '/goals', expectedUrl: '/goals' },
        { label: 'dca-plans', path: '/dca-plans', expectedUrl: '/dca-plans' },
        { label: 'price-alerts', path: '/price-alerts', expectedUrl: '/price-alerts' },
        { label: 'account', path: '/account', expectedUrl: '/account' },
        { label: 'settings', path: '/settings', expectedUrl: '/settings' },
    ];

    for (const route of navRoutes) {
        test(`navbar link to ${route.label} navigates correctly`, async ({ page }) => {
            await page.goto('/');
            await expect(page.locator('fingather-portfolio-total')).toBeVisible({ timeout: 10000 });

            // Angular renders routerLink as href attribute
            await page.locator(`nav a[href="${route.path}"]`).click();

            await expect(page).toHaveURL(new RegExp(route.expectedUrl.replace('/', '\\/')), { timeout: 10000 });
        });
    }
});
