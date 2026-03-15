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

    async gotoAddTransaction(): Promise<void> {
        await this.page.goto('/transactions/add-transaction');
        await this.page.waitForSelector('input#units', { timeout: 10000 });
    }

    async gotoAddDividend(): Promise<void> {
        await this.page.goto('/transactions/add-dividend');
        await this.page.waitForSelector('input#price', { timeout: 10000 });
    }

    async gotoImport(): Promise<void> {
        await this.page.goto('/transactions/import');
    }

    async expectTransactionFormLoaded(): Promise<void> {
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('input#units')).toBeVisible();
        await expect(this.page.locator('input#price')).toBeVisible();
    }

    async expectDividendFormLoaded(): Promise<void> {
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('input#price')).toBeVisible();
    }

    /** Select the first item from a fingather-select dropdown. */
    private async selectFirstInDropdown(componentSelector: string): Promise<void> {
        const component = this.page.locator(componentSelector);
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').first().click();
    }

    async selectFirstAsset(): Promise<void> {
        await this.selectFirstInDropdown('fingather-select#assetId');
    }

    async selectActionType(index: number): Promise<void> {
        const component = this.page.locator('fingather-type-select#actionType');
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').nth(index).click();
    }

    async fillDate(date: string): Promise<void> {
        const input = this.page.locator('input#actionCreated');
        await input.click(); // focus → changes type from 'text' to 'datetime-local'
        await input.evaluate((el: HTMLInputElement, val: string) => {
            el.type = 'datetime-local';
            el.value = val;
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }, date);
    }

    async fillUnits(units: string): Promise<void> {
        await this.page.locator('input#units').fill(units);
    }

    async fillPrice(price: string): Promise<void> {
        await this.page.locator('input#price').fill(price);
    }

    async selectFirstCurrency(fieldId: string = 'currencyId'): Promise<void> {
        await this.selectFirstInDropdown(`fingather-select#${fieldId}`);
    }

    async submitForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async expectRedirectedToList(): Promise<void> {
        await expect(this.page).toHaveURL('/transactions', { timeout: 10000 });
    }

    /** Wait for the transaction table to have at least one data row. */
    async waitForTableRows(): Promise<void> {
        await this.page.waitForSelector('fingather-transaction-list table tbody tr', { timeout: 15000 });
    }

    async getTransactionRowCount(): Promise<number> {
        await this.waitForTableRows();
        return this.page.locator('table tbody tr').count();
    }

    async clickEditFirstBuyOrSell(): Promise<void> {
        await this.waitForTableRows();
        await this.page.locator('table tbody tr a[href*="edit-transaction"]').first().click();
    }

    async clickDeleteFirst(): Promise<void> {
        await this.waitForTableRows();
        await this.page.locator('fingather-delete-button button').first().click();
    }

    async confirmDelete(): Promise<void> {
        await expect(this.page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await this.page.locator('.modal-footer button.btn-danger').click();
    }
}
