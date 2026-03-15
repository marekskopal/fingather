import { expect, test } from '@playwright/test';

import { GroupsPage } from './pages/groups.page';

test.describe('Groups list', () => {
    test('groups page loads', async ({ page }) => {
        const groups = new GroupsPage(page);
        await groups.goto();
        await groups.expectLoaded();
    });

    test('groups table is visible', async ({ page }) => {
        await page.goto('/settings/groups');
        await page.waitForSelector('.card', { timeout: 10000 });
        await page.waitForFunction(
            () => !!document.querySelector('table, .no-data'),
            { timeout: 15000 },
        );
        expect(true).toBeTruthy();
    });

    test('add group link is present', async ({ page }) => {
        await page.goto('/settings/groups');
        await expect(page.locator('a[href*="add-group"]')).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Add group', () => {
    test('add group form loads', async ({ page }) => {
        const groups = new GroupsPage(page);
        await groups.gotoAddGroup();
        await groups.expectAddFormLoaded();
    });

    test('add group form has name, color, and assets fields', async ({ page }) => {
        await page.goto('/settings/groups/add-group');
        await expect(page.locator('input#name')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('fingather-color-picker#color')).toBeVisible();
        await expect(page.locator('fingather-select-multi#assetIds')).toBeVisible();
    });

    test('create group and return to list', async ({ page }) => {
        const groups = new GroupsPage(page);
        await groups.gotoAddGroup();

        const hasAsset = await groups.hasAvailableAsset();
        if (!hasAsset) {
            test.skip(true, 'No unassigned assets available to create a group');
            return;
        }

        await groups.fillName('Test Group E2E');
        await groups.selectFirstColor();
        await groups.selectFirstAsset();
        await groups.submitForm();
        await groups.expectRedirectedToList();
        await groups.expectGroupVisible('Test Group E2E');

        // Cleanup
        await groups.deleteGroup('Test Group E2E');
    });
});

test.describe('Edit group', () => {
    test('edit group form loads with current values', async ({ page }) => {
        await page.goto('/settings/groups');
        await page.waitForSelector('.card', { timeout: 10000 });

        const editLink = page.locator('table tbody tr a[href*="edit-group"]').first();
        const isVisible = await editLink.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No groups found to edit');
            return;
        }

        await editLink.click();
        await expect(page).toHaveURL(/\/settings\/groups\/edit-group\/\d+/, { timeout: 10000 });
        await expect(page.locator('input#name')).toBeVisible({ timeout: 10000 });
    });

    test('edit group saves and returns to list', async ({ page }) => {
        const groups = new GroupsPage(page);
        await groups.gotoAddGroup();

        const hasAsset = await groups.hasAvailableAsset();
        if (!hasAsset) {
            test.skip(true, 'No unassigned assets available to create a group');
            return;
        }

        // Create
        await groups.fillName('Edit Test Group E2E');
        await groups.selectFirstColor();
        await groups.selectFirstAsset();
        await groups.submitForm();
        await groups.expectRedirectedToList();

        // Edit
        await groups.clickEditGroup('Edit Test Group E2E');
        await page.waitForSelector('input#name', { timeout: 10000 });
        await page.locator('input#name').fill('Edit Test Group E2E Updated');
        await groups.submitForm();
        await groups.expectRedirectedToList();
        await groups.expectGroupVisible('Edit Test Group E2E Updated');

        // Cleanup
        await groups.deleteGroup('Edit Test Group E2E Updated');
    });
});

test.describe('Delete group', () => {
    test('delete shows confirm dialog', async ({ page }) => {
        await page.goto('/settings/groups');
        await page.waitForSelector('.card', { timeout: 10000 });

        const deleteBtn = page.locator('fingather-delete-button button').first();
        const isVisible = await deleteBtn.isVisible().catch(() => false);
        if (!isVisible) {
            test.skip(true, 'No groups found to delete');
            return;
        }

        await deleteBtn.click();
        await expect(page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        // Cancel instead of confirming
        await page.locator('.modal-footer button.btn-secondary').click();
    });

    test('create and delete group', async ({ page }) => {
        const groups = new GroupsPage(page);
        await groups.gotoAddGroup();

        const hasAsset = await groups.hasAvailableAsset();
        if (!hasAsset) {
            test.skip(true, 'No unassigned assets available to create a group');
            return;
        }

        await groups.fillName('Delete Test Group E2E');
        await groups.selectFirstColor();
        await groups.selectFirstAsset();
        await groups.submitForm();
        await groups.expectRedirectedToList();

        const rowCountBefore = await groups.getGroupRowCount();

        await groups.deleteGroup('Delete Test Group E2E');

        await expect(page.locator('table tbody tr')).toHaveCount(rowCountBefore - 1, { timeout: 10000 });
    });
});
