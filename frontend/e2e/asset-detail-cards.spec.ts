import { expect, test, type Page } from '@playwright/test';

/**
 * Verifies that the asset detail page conditionally shows/hides the About,
 * DCF Valuation, and Fundamentals cards based on data availability.
 *
 * The e2e seed (backend/src/Command/E2eSeedCommand.php) creates three variants:
 *   - AAPL: description + fundamentals + DCF history     → all three visible
 *   - MSFT: description only                              → only About visible
 *   - NVDA: fundamentals + DCF history, no description    → About hidden, others visible
 */

const aboutCard = '.asset-detail-about';
const dcfCard = '.dcf-card';
const fundamentalsCard = '.asset-detail-fundamentals';

async function gotoAssetByTicker(page: Page, ticker: string): Promise<void> {
    await page.goto('/assets');
    await expect(page.locator('ul[role="tablist"]')).toBeVisible({ timeout: 15000 });

    const row = page
        .locator('fingather-opened-assets table tbody tr, fingather-opened-grouped-assets table tbody tr')
        .filter({ has: page.getByText(ticker, { exact: true }) })
        .first();
    await row.locator('a.btn-secondary').click();

    await expect(page).toHaveURL(/\/assets\/\d+/, { timeout: 10000 });
    await expect(page.locator('fingather-asset-value')).toBeVisible({ timeout: 10000 });
}

test.describe('Asset detail card visibility', () => {
    test('AAPL — about, DCF, and fundamentals cards are all visible', async ({ page }) => {
        await gotoAssetByTicker(page, 'AAPL');

        await expect(page.locator(aboutCard)).toBeVisible();
        await expect(page.locator(dcfCard)).toBeVisible();
        await expect(page.locator(fundamentalsCard)).toBeVisible();
    });

    test('MSFT — only the about card is visible (no DCF, no fundamentals)', async ({ page }) => {
        await gotoAssetByTicker(page, 'MSFT');

        await expect(page.locator(aboutCard)).toBeVisible();
        await expect(page.locator(dcfCard)).toHaveCount(0);
        await expect(page.locator(fundamentalsCard)).toHaveCount(0);
    });

    test('NVDA — DCF and fundamentals cards visible, about card hidden', async ({ page }) => {
        await gotoAssetByTicker(page, 'NVDA');

        await expect(page.locator(dcfCard)).toBeVisible();
        await expect(page.locator(fundamentalsCard)).toBeVisible();
        await expect(page.locator(aboutCard)).toHaveCount(0);
    });

    test('asset detail loads without throwing when fundamentals/DCF endpoints 404', async ({ page }) => {
        const pageErrors: Error[] = [];
        page.on('pageerror', (err) => pageErrors.push(err));

        await gotoAssetByTicker(page, 'MSFT');

        // Wait for the value component to render so the fundamentals/DCF requests have settled.
        await expect(page.locator('fingather-asset-value')).toBeVisible({ timeout: 10000 });

        expect(pageErrors).toEqual([]);
    });
});
