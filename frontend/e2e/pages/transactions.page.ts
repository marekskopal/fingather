import { expect, Page } from '@playwright/test';

export class TransactionsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/transactions');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/transactions');
        await expect(this.page.locator('fingather-transaction-list')).toBeVisible({ timeout: 10000 });
    }

    async expectRowCount(minRows: number): Promise<void> {
        await expect(this.page.locator('table tbody tr')).toHaveCount(minRows, { timeout: 10000 });
    }

    async openAddModal(): Promise<void> {
        await this.page.click('button:has-text("Add"), a:has-text("Add transaction"), mat-icon:has-text("add")');
        await expect(this.page.locator('.modal, fingather-add-edit-transaction')).toBeVisible();
    }

    async closeModal(): Promise<void> {
        const closeBtn = this.page.locator('.modal .btn-close, .modal button:has-text("Cancel")');
        if (await closeBtn.isVisible()) {
            await closeBtn.click();
        }
    }
}
