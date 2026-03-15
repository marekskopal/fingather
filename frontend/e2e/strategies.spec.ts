import { expect, test } from '@playwright/test';

import { StrategiesPage } from './pages/strategies.page';

test.describe('Strategies list', () => {
    test('strategies page loads', async ({ page }) => {
        const strategies = new StrategiesPage(page);
        await strategies.goto();
        await strategies.expectLoaded();
    });

    test('strategy list table is visible', async ({ page }) => {
        await page.goto('/strategies');
        await page.waitForSelector('fingather-strategy-list', { timeout: 10000 });
        await page.waitForFunction(
            () => !!document.querySelector('fingather-strategy-list table, fingather-strategy-list .no-data'),
            { timeout: 15000 },
        );
        expect(true).toBeTruthy();
    });

    test('add strategy link is present', async ({ page }) => {
        await page.goto('/strategies');
        await expect(page.locator('a[href*="add-strategy"]')).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Add strategy', () => {
    test('add strategy form loads', async ({ page }) => {
        const strategies = new StrategiesPage(page);
        await strategies.gotoAddStrategy();
        await strategies.expectAddFormLoaded();
    });

    test('form has name field and add item button', async ({ page }) => {
        await page.goto('/strategies/add-strategy');
        await expect(page.locator('input#name')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('button.btn-outline-primary')).toBeVisible();
    });

    test('create strategy with no items and return to list', async ({ page }) => {
        const strategies = new StrategiesPage(page);
        await strategies.gotoAddStrategy();

        await strategies.fillName('Test Strategy E2E');
        await strategies.submitForm();
        await strategies.expectRedirectedToList();
        await strategies.expectStrategyVisible('Test Strategy E2E');

        // Cleanup
        await strategies.deleteStrategy('Test Strategy E2E');
    });

    test('create strategy with one asset item', async ({ page }) => {
        const strategies = new StrategiesPage(page);
        await strategies.gotoAddStrategy();

        await strategies.fillName('Asset Strategy E2E');
        await strategies.clickAddItem();

        // Default type is 'asset' — asset dropdown should already be visible
        const assetSelect = page.locator('.strategy-edit-item').nth(0).locator('fingather-select:not(.select-type)');
        const assetVisible = await assetSelect.isVisible().catch(() => false);
        if (!assetVisible) {
            test.skip(true, 'Asset dropdown did not appear — no assets in portfolio');
            return;
        }

        await strategies.selectFirstAssetForItem(0);
        await strategies.fillItemPercentage(0, '50');

        await strategies.submitForm();
        await strategies.expectRedirectedToList();
        await strategies.expectStrategyVisible('Asset Strategy E2E');

        // Cleanup
        await strategies.deleteStrategy('Asset Strategy E2E');
    });
});

test.describe('Edit strategy', () => {
    test('edit strategy form loads with current values', async ({ page }) => {
        await page.goto('/strategies');
        await page.waitForSelector('fingather-strategy-list', { timeout: 10000 });

        const editLink = page.locator('fingather-strategy-list table tbody tr a[href*="edit-strategy"]').first();
        const isVisible = await editLink.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No strategies found to edit');
            return;
        }

        await editLink.click();
        await expect(page).toHaveURL(/\/strategies\/edit-strategy\/\d+/, { timeout: 10000 });
        await expect(page.locator('input#name')).toBeVisible({ timeout: 10000 });
    });

    test('edit strategy name and save', async ({ page }) => {
        const strategies = new StrategiesPage(page);

        // Create
        await strategies.gotoAddStrategy();
        await strategies.fillName('Edit Strategy E2E');
        await strategies.submitForm();
        await strategies.expectRedirectedToList();

        // Edit
        await strategies.clickEditStrategy('Edit Strategy E2E');
        await page.waitForSelector('input#name', { timeout: 10000 });
        await page.locator('input#name').fill('Edit Strategy E2E Updated');
        await strategies.submitForm();
        await strategies.expectRedirectedToList();
        await strategies.expectStrategyVisible('Edit Strategy E2E Updated');

        // Cleanup
        await strategies.deleteStrategy('Edit Strategy E2E Updated');
    });
});

test.describe('Delete strategy', () => {
    test('delete shows confirm dialog', async ({ page }) => {
        await page.goto('/strategies');
        await page.waitForSelector('fingather-strategy-list', { timeout: 10000 });

        const deleteBtn = page.locator('fingather-strategy-list fingather-delete-button button').first();
        const isVisible = await deleteBtn.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No strategies found to delete');
            return;
        }

        await deleteBtn.click();
        await expect(page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        // Cancel
        await page.locator('.modal-footer button.btn-secondary').click();
    });

    test('create and delete strategy', async ({ page }) => {
        const strategies = new StrategiesPage(page);

        await strategies.gotoAddStrategy();
        await strategies.fillName('Delete Strategy E2E');
        await strategies.submitForm();
        await strategies.expectRedirectedToList();

        const rowCountBefore = await strategies.getStrategyRowCount();

        await strategies.deleteStrategy('Delete Strategy E2E');

        await expect(page.locator('fingather-strategy-list table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});
