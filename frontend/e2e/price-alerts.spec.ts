import { expect, test } from '@playwright/test';

import { PriceAlertsPage } from './pages/price-alerts.page';

test.describe('Price alerts list', () => {
    test('price alerts page loads', async ({ page }) => {
        const priceAlerts = new PriceAlertsPage(page);
        await priceAlerts.goto();
        await priceAlerts.expectLoaded();
    });

    test('table is visible or empty state rendered', async ({ page }) => {
        await page.goto('/price-alerts');
        await page.waitForSelector('.card', { timeout: 10000 });
        await page.waitForFunction(
            () => !!document.querySelector('table, .no-data'),
            { timeout: 15000 },
        );
        expect(true).toBeTruthy();
    });

    test('add price alert link is present', async ({ page }) => {
        await page.goto('/price-alerts');
        await expect(page.locator('a[href*="add-price-alert"]')).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Add price alert — Portfolio type', () => {
    test('add price alert form loads', async ({ page }) => {
        const priceAlerts = new PriceAlertsPage(page);
        await priceAlerts.gotoAddPriceAlert();
        await priceAlerts.expectAddFormLoaded();
    });

    test('form has type, condition, targetValue and recurrence fields', async ({ page }) => {
        await page.goto('/price-alerts/add-price-alert');
        await expect(page.locator('fingather-select#type')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('fingather-select#condition')).toBeVisible();
        await expect(page.locator('input#targetValue')).toBeVisible();
        await expect(page.locator('fingather-select#recurrence')).toBeVisible();
    });

    test('create portfolio price alert and return to list', async ({ page }) => {
        const priceAlerts = new PriceAlertsPage(page);
        await priceAlerts.gotoAddPriceAlert();

        await priceAlerts.selectType('Portfolio');
        await priceAlerts.selectFirstPortfolio();
        await priceAlerts.selectFirstCondition();
        await priceAlerts.fillTargetValue('10000');
        await priceAlerts.selectFirstRecurrence();

        await priceAlerts.submitForm();
        await priceAlerts.expectRedirectedToList();

        const rowCountBefore = await priceAlerts.getPriceAlertRowCount();

        // Cleanup: delete the newly created alert (last or first)
        await priceAlerts.clickDeleteFirst();
        await priceAlerts.confirmDelete();

        await expect(page.locator('table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});

test.describe('Add price alert — Price type', () => {
    test('price type shows ticker search selector', async ({ page }) => {
        await page.goto('/price-alerts/add-price-alert');
        await page.waitForSelector('fingather-select#type', { timeout: 10000 });

        const component = page.locator('fingather-select#type');
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        const items = component.locator('.dropdown-menu.show button.dropdown-item');
        const count = await items.count();
        let priceItemClicked = false;
        for (let i = 0; i < count; i++) {
            const text = await items.nth(i).textContent();
            if (text?.toLowerCase().includes('price')) {
                await items.nth(i).click();
                priceItemClicked = true;
                break;
            }
        }
        if (!priceItemClicked) {
            await items.first().click();
        }

        await expect(page.locator('fingather-ticker-search-selector#tickerId')).toBeVisible({ timeout: 5000 });
    });
});

test.describe('Edit price alert', () => {
    test('edit price alert form loads with current values', async ({ page }) => {
        await page.goto('/price-alerts');
        await page.waitForSelector('.card', { timeout: 10000 });

        const editLink = page.locator('table tbody tr a[href*="edit-price-alert"]').first();
        const isVisible = await editLink.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No price alerts found to edit');
            return;
        }

        await editLink.click();
        await expect(page).toHaveURL(/\/price-alerts\/edit-price-alert\/\d+/, { timeout: 10000 });
        await expect(page.locator('fingather-select#type')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('input#targetValue')).toBeVisible();
    });

    test('edit price alert saves and returns to list', async ({ page }) => {
        const priceAlerts = new PriceAlertsPage(page);

        // Create
        await priceAlerts.gotoAddPriceAlert();
        await priceAlerts.selectType('Portfolio');
        await priceAlerts.selectFirstPortfolio();
        await priceAlerts.selectFirstCondition();
        await priceAlerts.fillTargetValue('5000');
        await priceAlerts.selectFirstRecurrence();
        await priceAlerts.submitForm();
        await priceAlerts.expectRedirectedToList();

        // Edit first alert
        await priceAlerts.clickEditFirst();
        await page.waitForSelector('input#targetValue', { timeout: 10000 });
        await page.locator('input#targetValue').fill('6000');
        await priceAlerts.submitForm();
        await priceAlerts.expectRedirectedToList();

        // Cleanup
        await priceAlerts.clickDeleteFirst();
        await priceAlerts.confirmDelete();
    });
});

test.describe('Delete price alert', () => {
    test('delete shows confirm dialog', async ({ page }) => {
        await page.goto('/price-alerts');
        await page.waitForSelector('.card', { timeout: 10000 });

        const deleteBtn = page.locator('fingather-delete-button button').first();
        const isVisible = await deleteBtn.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No price alerts found to delete');
            return;
        }

        await deleteBtn.click();
        await expect(page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        // Cancel
        await page.locator('.modal-footer button.btn-secondary').click();
    });

    test('create and delete price alert', async ({ page }) => {
        const priceAlerts = new PriceAlertsPage(page);

        await priceAlerts.gotoAddPriceAlert();
        await priceAlerts.selectType('Portfolio');
        await priceAlerts.selectFirstPortfolio();
        await priceAlerts.selectFirstCondition();
        await priceAlerts.fillTargetValue('1000');
        await priceAlerts.selectFirstRecurrence();
        await priceAlerts.submitForm();
        await priceAlerts.expectRedirectedToList();

        const rowCountBefore = await priceAlerts.getPriceAlertRowCount();

        await priceAlerts.clickDeleteFirst();
        await priceAlerts.confirmDelete();

        await expect(page.locator('table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});
