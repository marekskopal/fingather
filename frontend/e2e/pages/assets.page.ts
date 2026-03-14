import { expect, Page } from '@playwright/test';

export class AssetsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/assets');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/assets');
        await expect(this.page.locator('ul[role="tablist"]')).toBeVisible({ timeout: 10000 });
    }

    async clickFirstAsset(): Promise<void> {
        await this.page.locator('table tbody tr:first-child, .asset-item:first-child').click();
    }

    async expectDetailVisible(): Promise<void> {
        await expect(this.page).toHaveURL(/\/assets\/\d+/);
        await expect(this.page.locator('fingather-asset-value, .asset-detail')).toBeVisible({ timeout: 10000 });
    }
}
