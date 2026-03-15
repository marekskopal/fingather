import { expect, test } from '@playwright/test';

import { OverviewsPage } from './pages/overviews.page';

test.describe('Overviews list', () => {
    test('overviews page loads', async ({ page }) => {
        const overviews = new OverviewsPage(page);
        await overviews.goto();
        await overviews.expectLoaded();
    });

    test('overviews table is visible', async ({ page }) => {
        const overviews = new OverviewsPage(page);
        await overviews.goto();
        await overviews.expectTableVisible();
    });
});

test.describe('Tax report', () => {
    test('tax report link navigates to tax report page', async ({ page }) => {
        const overviews = new OverviewsPage(page);
        await overviews.goto();
        await overviews.expectLoaded();

        const hasRows = await overviews.hasYearRows();
        if (!hasRows) {
            test.skip(true, 'No yearly overview data found to navigate to tax report');
            return;
        }

        await overviews.clickFirstTaxReport();
        await overviews.expectTaxReportLoaded();
    });

    test('tax report page has export buttons', async ({ page }) => {
        await page.goto('/overviews');
        await page.waitForSelector('table', { timeout: 15000 });

        const taxReportLink = page.locator('a[href*="tax-report"]').first();
        const isVisible = await taxReportLink.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No yearly overview data found');
            return;
        }

        await taxReportLink.click();
        await expect(page).toHaveURL(/\/overviews\/tax-report\/\d+/, { timeout: 10000 });
        await expect(page.locator('button', { hasText: /xlsx|pdf/i }).first()).toBeVisible({ timeout: 10000 });
    });
});
