import { expect, test } from '@playwright/test';

import { GoalsPage } from './pages/goals.page';

test.describe('Goals list', () => {
    test('goals page loads', async ({ page }) => {
        const goals = new GoalsPage(page);
        await goals.goto();
        await goals.expectLoaded();
    });

    test('table visible or empty state', async ({ page }) => {
        await page.goto('/goals');
        await page.waitForSelector('.card', { timeout: 10000 });
        await page.waitForFunction(
            () => !!document.querySelector('table, .no-data'),
            { timeout: 15000 },
        );
        expect(true).toBeTruthy();
    });

    test('add goal link is present', async ({ page }) => {
        await page.goto('/goals');
        await expect(page.locator('a[href*="add-goal"]')).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Add goal', () => {
    test('add goal form loads', async ({ page }) => {
        const goals = new GoalsPage(page);
        await goals.gotoAddGoal();
        await goals.expectAddFormLoaded();
    });

    test('create goal and return to list', async ({ page }) => {
        const goals = new GoalsPage(page);
        await goals.gotoAddGoal();

        await goals.selectFirstPortfolio();
        await goals.selectFirstType();
        await goals.fillTargetValue('100000');
        await goals.fillDeadline('2030-12-31');

        await goals.submitForm();
        await goals.expectRedirectedToList();

        const rowCountBefore = await goals.getGoalRowCount();

        // Cleanup
        await goals.clickDeleteFirst();
        await goals.confirmDelete();

        await expect(page.locator('table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});

test.describe('Edit goal', () => {
    test('edit goal form loads with current values', async ({ page }) => {
        await page.goto('/goals');
        await page.waitForSelector('.card', { timeout: 10000 });

        const editLink = page.locator('table tbody tr a[href*="edit-goal"]').first();
        const isVisible = await editLink.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No goals found to edit');
            return;
        }

        await editLink.click();
        await expect(page).toHaveURL(/\/goals\/edit-goal\/\d+/, { timeout: 10000 });
        await expect(page.locator('fingather-select#portfolioId')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('input#targetValue')).toBeVisible();
    });

    test('edit goal saves and returns to list', async ({ page }) => {
        const goals = new GoalsPage(page);

        // Create
        await goals.gotoAddGoal();
        await goals.selectFirstPortfolio();
        await goals.selectFirstType();
        await goals.fillTargetValue('50000');
        await goals.fillDeadline('2028-06-30');
        await goals.submitForm();
        await goals.expectRedirectedToList();

        // Edit
        await goals.clickEditFirst();
        await page.waitForSelector('input#targetValue', { timeout: 10000 });
        await page.locator('input#targetValue').fill('75000');
        await goals.submitForm();
        await goals.expectRedirectedToList();

        // Cleanup
        await goals.clickDeleteFirst();
        await goals.confirmDelete();
    });
});

test.describe('Delete goal', () => {
    test('delete shows confirm dialog', async ({ page }) => {
        await page.goto('/goals');
        await page.waitForSelector('.card', { timeout: 10000 });

        const deleteBtn = page.locator('fingather-delete-button button').first();
        const isVisible = await deleteBtn.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No goals found to delete');
            return;
        }

        await deleteBtn.click();
        await expect(page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        // Cancel
        await page.locator('.modal-footer button.btn-secondary').click();
    });

    test('create and delete goal', async ({ page }) => {
        const goals = new GoalsPage(page);

        await goals.gotoAddGoal();
        await goals.selectFirstPortfolio();
        await goals.selectFirstType();
        await goals.fillTargetValue('25000');
        await goals.fillDeadline('2027-01-01');
        await goals.submitForm();
        await goals.expectRedirectedToList();

        const rowCountBefore = await goals.getGoalRowCount();

        await goals.clickDeleteFirst();
        await goals.confirmDelete();

        await expect(page.locator('table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});
