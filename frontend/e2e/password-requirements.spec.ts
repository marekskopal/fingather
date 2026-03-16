import {expect, test} from '@playwright/test';

import {AccountPage} from './pages/account.page';
import {SignUpPage} from './pages/sign-up.page';

// Sign-up and reset-password are public pages — no stored auth needed
test.describe('Password requirements — sign-up', () => {
    test.use({ storageState: { cookies: [], origins: [] } });

    test('checklist is hidden when password field is empty', async ({ page }) => {
        const signUp = new SignUpPage(page);
        await signUp.goto();
        await signUp.expectRequirementsHidden();
    });

    test('checklist appears when password field has a value', async ({ page }) => {
        const signUp = new SignUpPage(page);
        await signUp.goto();
        await signUp.fillPassword('a');
        await signUp.expectRequirementsVisible();
    });

    test('all items are unmet for a weak password', async ({ page }) => {
        const signUp = new SignUpPage(page);
        await signUp.goto();
        await signUp.fillPassword('weak');

        // minLength, uppercase, digit, specialChar all unmet
        await signUp.expectRequirementUnmet(0); // minLength
        await signUp.expectRequirementUnmet(1); // uppercase
        await signUp.expectRequirementUnmet(3); // digit
        await signUp.expectRequirementUnmet(4); // specialChar
    });

    test('minLength item turns met at 8+ characters', async ({ page }) => {
        const signUp = new SignUpPage(page);
        await signUp.goto();
        await signUp.fillPassword('abcdefgh');
        await signUp.expectRequirementMet(0); // minLength
    });

    test('uppercase item turns met when uppercase letter is added', async ({ page }) => {
        const signUp = new SignUpPage(page);
        await signUp.goto();
        await signUp.fillPassword('Abcdefgh');
        await signUp.expectRequirementMet(1); // uppercase
    });

    test('all items are met for a strong password', async ({ page }) => {
        const signUp = new SignUpPage(page);
        await signUp.goto();
        await signUp.fillPassword('ValidPass1!');
        await signUp.expectAllRequirementsMet();
    });

    test('form blocks submission with a weak password', async ({ page }) => {
        const signUp = new SignUpPage(page);
        await signUp.goto();
        await page.fill('#name', 'Test User');
        await page.fill('#email', 'test@example.com');
        await signUp.fillPassword('weakpassword');
        await signUp.submitForm();

        // still on sign-up page
        await expect(page).toHaveURL(/sign-up/);
        await signUp.expectPasswordError();
    });
});

test.describe('Password requirements — account info', () => {
    test('checklist is hidden when password field is empty', async ({ page }) => {
        const account = new AccountPage(page);
        await account.goto();
        await account.expectInfoTableVisible();
        await account.clickEdit();
        await account.expectEditFormVisible();
        await account.expectRequirementsHidden();
    });

    test('checklist appears when password field has a value', async ({ page }) => {
        const account = new AccountPage(page);
        await account.goto();
        await account.expectInfoTableVisible();
        await account.clickEdit();
        await account.expectEditFormVisible();
        await account.fillPassword('a');
        await account.expectRequirementsVisible();
    });

    test('all items are met for a strong password', async ({ page }) => {
        const account = new AccountPage(page);
        await account.goto();
        await account.expectInfoTableVisible();
        await account.clickEdit();
        await account.expectEditFormVisible();
        await account.fillPassword('ValidPass1!');
        await account.expectAllRequirementsMet();
    });

    test('form blocks submission with a weak password', async ({ page }) => {
        const account = new AccountPage(page);
        await account.goto();
        await account.expectInfoTableVisible();
        await account.clickEdit();
        await account.expectEditFormVisible();
        await account.fillPassword('weakpassword');
        await account.submitEdit();
        await account.expectPasswordError();
    });
});
