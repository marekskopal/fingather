import { expect, test } from '@playwright/test';

import { PortfoliosPage } from './pages/portfolios.page';

test.describe('Portfolios', () => {
    test('portfolios page loads', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);
        await portfolios.goto();
        await portfolios.expectLoaded();
    });

    test('default portfolio is visible', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);
        await portfolios.goto();
        // At least one portfolio entry should exist
        await expect(page.locator('.portfolio-list .portfolio')).toHaveCount(1, { timeout: 10000 });
    });

    test('add portfolio link is present', async ({ page }) => {
        await page.goto('/portfolios');
        const addLink = page.locator('a[href$="/portfolios/add-portfolio"]');
        await expect(addLink).toBeVisible({ timeout: 10000 });
    });
});
