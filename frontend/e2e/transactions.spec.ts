import { expect, test } from '@playwright/test';

import { TransactionsPage } from './pages/transactions.page';

test.describe('Transactions', () => {
    test('transactions page loads', async ({ page }) => {
        const transactions = new TransactionsPage(page);
        await transactions.goto();
        await transactions.expectLoaded();
    });

    test('transactions page shows table or empty state', async ({ page }) => {
        await page.goto('/transactions');
        await expect(page).toHaveURL('/transactions');
        // Give Angular time to load
        await page.waitForTimeout(1000);
        const hasRows = await page.locator('table tbody tr').count() > 0;
        const hasEmpty = await page.locator('[class*="empty"], .no-data').isVisible().catch(() => false);
        expect(hasRows || hasEmpty).toBeTruthy();
    });

    test('add transaction button is present', async ({ page }) => {
        await page.goto('/transactions');
        const addBtn = page.locator('button:has-text("Add"), a:has-text("Add"), mat-icon:has-text("add")');
        await expect(addBtn.first()).toBeVisible({ timeout: 10000 });
    });
});
