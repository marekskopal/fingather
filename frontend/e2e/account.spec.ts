import { expect, test } from '@playwright/test';

import { AccountPage } from './pages/account.page';

test.describe('Account page', () => {
    test('account page loads', async ({ page }) => {
        const account = new AccountPage(page);
        await account.goto();
        await account.expectLoaded();
    });

    test('account info table shows user data', async ({ page }) => {
        const account = new AccountPage(page);
        await account.goto();
        await account.expectInfoTableVisible();
    });

    test('clicking edit shows edit form', async ({ page }) => {
        const account = new AccountPage(page);
        await account.goto();
        await account.expectInfoTableVisible();
        await account.clickEdit();
        await account.expectEditFormVisible();
    });

    test('cancelling edit returns to info view', async ({ page }) => {
        const account = new AccountPage(page);
        await account.goto();
        await account.expectInfoTableVisible();
        await account.clickEdit();
        await account.expectEditFormVisible();
        await account.clickCancelEdit();
        await account.expectInfoViewVisible();
    });
});
