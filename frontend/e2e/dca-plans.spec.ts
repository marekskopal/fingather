import { expect, test } from '@playwright/test';

import { DcaPlansPage } from './pages/dca-plans.page';

test.describe('DCA Plans list', () => {
    test('dca plans page loads', async ({ page }) => {
        const dcaPlans = new DcaPlansPage(page);
        await dcaPlans.goto();
        await dcaPlans.expectLoaded();
    });

    test('table visible or empty state', async ({ page }) => {
        await page.goto('/dca-plans');
        await page.waitForSelector('.card', { timeout: 10000 });
        await page.waitForFunction(
            () => !!document.querySelector('table, .no-data'),
            { timeout: 15000 },
        );
        expect(true).toBeTruthy();
    });

    test('add dca plan link is present', async ({ page }) => {
        await page.goto('/dca-plans');
        await expect(page.locator('a[href*="add-dca-plan"]')).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Add DCA plan — Portfolio target', () => {
    test('add dca plan form loads', async ({ page }) => {
        const dcaPlans = new DcaPlansPage(page);
        await dcaPlans.gotoAddDcaPlan();
        await dcaPlans.expectAddFormLoaded();
    });

    test('create portfolio DCA plan and return to list', async ({ page }) => {
        const dcaPlans = new DcaPlansPage(page);
        await dcaPlans.gotoAddDcaPlan();

        await dcaPlans.selectTargetType('Portfolio');
        await dcaPlans.fillAmount('500');
        await dcaPlans.selectFirstCurrency();
        await dcaPlans.fillIntervalMonths('1');
        await dcaPlans.fillStartDate('2025-01-01');

        await dcaPlans.submitForm();
        await dcaPlans.expectRedirectedToList();

        const rowCountBefore = await dcaPlans.getPlanRowCount();

        // Cleanup
        await dcaPlans.clickDeleteFirst();
        await dcaPlans.confirmDelete();

        await expect(page.locator('table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});

test.describe('Add DCA plan — conditional fields', () => {
    test('selecting Asset targetType shows assetId select', async ({ page }) => {
        await page.goto('/dca-plans/add-dca-plan');
        await page.waitForSelector('fingather-select#targetType', { timeout: 10000 });

        const dcaPlans = new DcaPlansPage(page);
        await dcaPlans.selectTargetType('Asset');

        await expect(page.locator('fingather-select#assetId')).toBeVisible({ timeout: 5000 });
    });

    test('selecting Group targetType shows groupId select', async ({ page }) => {
        await page.goto('/dca-plans/add-dca-plan');
        await page.waitForSelector('fingather-select#targetType', { timeout: 10000 });

        const dcaPlans = new DcaPlansPage(page);
        await dcaPlans.selectTargetType('Group');

        await expect(page.locator('fingather-select#groupId')).toBeVisible({ timeout: 5000 });
    });

    test('selecting Strategy targetType shows strategyId select', async ({ page }) => {
        await page.goto('/dca-plans/add-dca-plan');
        await page.waitForSelector('fingather-select#targetType', { timeout: 10000 });

        const dcaPlans = new DcaPlansPage(page);
        await dcaPlans.selectTargetType('Strategy');

        await expect(page.locator('fingather-select#strategyId')).toBeVisible({ timeout: 5000 });
    });
});

test.describe('Edit DCA plan', () => {
    test('edit dca plan form loads with current values', async ({ page }) => {
        await page.goto('/dca-plans');
        await page.waitForSelector('.card', { timeout: 10000 });

        const editLink = page.locator('table tbody tr a[href*="edit-dca-plan"]').first();
        const isVisible = await editLink.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No DCA plans found to edit');
            return;
        }

        await editLink.click();
        await expect(page).toHaveURL(/\/dca-plans\/edit-dca-plan\/\d+/, { timeout: 10000 });
        await expect(page.locator('fingather-select#targetType')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('input#amount')).toBeVisible();
    });

    test('edit dca plan and save', async ({ page }) => {
        const dcaPlans = new DcaPlansPage(page);

        // Create
        await dcaPlans.gotoAddDcaPlan();
        await dcaPlans.selectTargetType('Portfolio');
        await dcaPlans.fillAmount('200');
        await dcaPlans.selectFirstCurrency();
        await dcaPlans.fillIntervalMonths('3');
        await dcaPlans.fillStartDate('2025-03-01');
        await dcaPlans.submitForm();
        await dcaPlans.expectRedirectedToList();

        // Edit
        await dcaPlans.clickEditFirst();
        await page.waitForSelector('input#amount', { timeout: 10000 });
        await page.locator('input#amount').fill('300');
        await dcaPlans.submitForm();
        await dcaPlans.expectRedirectedToList();

        // Cleanup
        await dcaPlans.clickDeleteFirst();
        await dcaPlans.confirmDelete();
    });
});

test.describe('Delete DCA plan', () => {
    test('delete shows confirm dialog', async ({ page }) => {
        await page.goto('/dca-plans');
        await page.waitForSelector('.card', { timeout: 10000 });

        const deleteBtn = page.locator('fingather-delete-button button').first();
        const isVisible = await deleteBtn.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No DCA plans found to delete');
            return;
        }

        await deleteBtn.click();
        await expect(page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        // Cancel
        await page.locator('.modal-footer button.btn-secondary').click();
    });

    test('create and delete dca plan', async ({ page }) => {
        const dcaPlans = new DcaPlansPage(page);

        await dcaPlans.gotoAddDcaPlan();
        await dcaPlans.selectTargetType('Portfolio');
        await dcaPlans.fillAmount('100');
        await dcaPlans.selectFirstCurrency();
        await dcaPlans.fillIntervalMonths('6');
        await dcaPlans.fillStartDate('2025-06-01');
        await dcaPlans.submitForm();
        await dcaPlans.expectRedirectedToList();

        const rowCountBefore = await dcaPlans.getPlanRowCount();

        await dcaPlans.clickDeleteFirst();
        await dcaPlans.confirmDelete();

        await expect(page.locator('table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});
