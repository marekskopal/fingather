import { expect, Page } from '@playwright/test';

export class StrategiesPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/strategies');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/strategies');
        await expect(this.page.locator('fingather-strategy-list')).toBeVisible({ timeout: 10000 });
    }

    async gotoAddStrategy(): Promise<void> {
        await this.page.goto('/strategies/add-strategy');
        await this.page.waitForSelector('input#name', { timeout: 10000 });
    }

    async expectAddFormLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/strategies/add-strategy');
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('input#name')).toBeVisible();
    }

    async fillName(name: string): Promise<void> {
        await this.page.locator('input#name').fill(name);
    }

    async clickAddItem(): Promise<void> {
        await this.page.locator('button.btn-outline-primary').click();
        // Wait for the new item row to appear
        await this.page.waitForSelector('.strategy-edit-item', { timeout: 5000 });
    }

    async selectItemType(index: number, type: 'asset' | 'group'): Promise<void> {
        // Use class-based selector — [id] dynamic binding does NOT set the HTML attribute on the host element
        const component = this.page.locator('.strategy-edit-item').nth(index).locator('fingather-select.select-type');
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        const items = component.locator('.dropdown-menu.show button.dropdown-item');
        const count = await items.count();
        for (let i = 0; i < count; i++) {
            const text = await items.nth(i).textContent();
            if (text?.toLowerCase().includes(type)) {
                await items.nth(i).click();
                return;
            }
        }
        await items.first().click();
    }

    async selectFirstAssetForItem(index: number): Promise<void> {
        // The assetId select is the second fingather-select in the item row (no class, unlike select-type)
        const component = this.page.locator('.strategy-edit-item').nth(index).locator('fingather-select:not(.select-type)');
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').first().click();
    }

    async fillItemPercentage(index: number, value: string): Promise<void> {
        await this.page.locator('.strategy-edit-item').nth(index).locator('input[type="number"]').fill(value);
    }

    async submitForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async expectRedirectedToList(): Promise<void> {
        await expect(this.page).toHaveURL('/strategies', { timeout: 10000 });
    }

    async expectStrategyVisible(name: string): Promise<void> {
        await expect(this.page.locator('fingather-strategy-list table tbody tr td', { hasText: name })).toBeVisible({ timeout: 10000 });
    }

    async getStrategyRowCount(): Promise<number> {
        await this.page.waitForSelector('fingather-strategy-list table', { timeout: 10000 });
        return this.page.locator('fingather-strategy-list table tbody tr').count();
    }

    async clickEditStrategy(name: string): Promise<void> {
        const row = this.page.locator('fingather-strategy-list table tbody tr').filter({ hasText: name });
        await row.locator('a[href*="edit-strategy"]').click();
    }

    async deleteStrategy(name: string): Promise<void> {
        const row = this.page.locator('fingather-strategy-list table tbody tr').filter({ hasText: name });
        await row.locator('fingather-delete-button button').click();
        await expect(this.page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await this.page.locator('.modal-footer button.btn-danger').click();
        await expect(this.page.locator('fingather-strategy-list table tbody tr td', { hasText: name })).not.toBeVisible({ timeout: 10000 });
    }
}
