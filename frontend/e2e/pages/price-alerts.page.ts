import { expect, Page } from '@playwright/test';

export class PriceAlertsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/price-alerts');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/price-alerts');
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async gotoAddPriceAlert(): Promise<void> {
        await this.page.goto('/price-alerts/add-price-alert');
        await this.page.waitForSelector('fingather-select#type', { timeout: 10000 });
    }

    async expectAddFormLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/price-alerts/add-price-alert');
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('fingather-select#type')).toBeVisible();
    }

    private async selectInDropdown(id: string, labelText: string): Promise<void> {
        const component = this.page.locator(`fingather-select#${id}`);
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        const items = component.locator('.dropdown-menu.show button.dropdown-item');
        const count = await items.count();
        for (let i = 0; i < count; i++) {
            const text = await items.nth(i).textContent();
            if (text?.toLowerCase().includes(labelText.toLowerCase())) {
                await items.nth(i).click();
                return;
            }
        }
        // Fallback: click first
        await items.first().click();
    }

    private async selectFirstInDropdown(id: string): Promise<void> {
        const component = this.page.locator(`fingather-select#${id}`);
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').first().click();
    }

    async selectType(labelText: string): Promise<void> {
        await this.selectInDropdown('type', labelText);
    }

    async selectFirstPortfolio(): Promise<void> {
        await this.selectFirstInDropdown('portfolioId');
    }

    async selectFirstCondition(): Promise<void> {
        await this.selectFirstInDropdown('condition');
    }

    async fillTargetValue(value: string): Promise<void> {
        await this.page.locator('input#targetValue').fill(value);
    }

    async selectFirstRecurrence(): Promise<void> {
        await this.selectFirstInDropdown('recurrence');
    }

    async submitForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async expectRedirectedToList(): Promise<void> {
        await expect(this.page).toHaveURL('/price-alerts', { timeout: 10000 });
    }

    async getPriceAlertRowCount(): Promise<number> {
        await this.page.waitForSelector('table', { timeout: 10000 });
        return this.page.locator('table tbody tr').count();
    }

    async clickEditFirst(): Promise<void> {
        await this.page.waitForSelector('table tbody tr a[href*="edit-price-alert"]', { timeout: 10000 });
        await this.page.locator('table tbody tr a[href*="edit-price-alert"]').first().click();
    }

    async clickDeleteFirst(): Promise<void> {
        await this.page.waitForSelector('fingather-delete-button button', { timeout: 10000 });
        await this.page.locator('fingather-delete-button button').first().click();
    }

    async confirmDelete(): Promise<void> {
        await expect(this.page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await this.page.locator('.modal-footer button.btn-danger').click();
    }
}
