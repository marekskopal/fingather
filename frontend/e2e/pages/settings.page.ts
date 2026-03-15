import { expect, Page } from '@playwright/test';

export class SettingsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/settings');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings/);
    }

    async gotoGroups(): Promise<void> {
        await this.page.goto('/settings/groups');
    }

    async expectGroupsLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings\/groups/);
    }

    async gotoBenchmarkAssets(): Promise<void> {
        await this.page.goto('/settings/benchmark-assets');
    }

    async expectBenchmarkAssetsLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings\/benchmark-assets/);
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async gotoImportMappings(): Promise<void> {
        await this.page.goto('/settings/import-mappings');
    }

    async expectImportMappingsLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings\/import-mappings/);
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async gotoApiKeys(): Promise<void> {
        await this.page.goto('/settings/api-keys');
    }

    async expectApiKeysLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings\/api-keys/);
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async gotoAddApiKey(): Promise<void> {
        await this.page.goto('/settings/api-keys/add-api-key');
        await this.page.waitForSelector('fingather-select#type', { timeout: 10000 });
    }

    async expectAddApiKeyFormLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings\/api-keys\/add-api-key/);
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('fingather-select#type')).toBeVisible();
        await expect(this.page.locator('input#apiKey')).toBeVisible();
    }

    async selectFirstApiKeyType(): Promise<void> {
        const component = this.page.locator('fingather-select#type');
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').first().click();
    }

    async fillApiKeyValue(value: string): Promise<void> {
        await this.page.locator('input#apiKey').fill(value);
    }

    async submitForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async expectRedirectedToApiKeyList(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings\/api-keys$/, { timeout: 10000 });
    }

    async getApiKeyRowCount(): Promise<number> {
        await this.page.waitForSelector('table', { timeout: 10000 });
        return this.page.locator('table tbody tr').count();
    }

    async clickDeleteFirstApiKey(): Promise<void> {
        await this.page.waitForSelector('fingather-delete-button button', { timeout: 10000 });
        await this.page.locator('fingather-delete-button button').first().click();
    }

    async confirmDelete(): Promise<void> {
        await expect(this.page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await this.page.locator('.modal-footer button.btn-danger').click();
    }
}
