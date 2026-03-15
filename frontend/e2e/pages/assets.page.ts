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
        await this.page.locator('fingather-opened-assets table tbody tr a.btn-secondary, fingather-opened-grouped-assets table tbody tr a.btn-secondary').first().click();
    }

    async hasAssets(): Promise<boolean> {
        return this.page.locator('fingather-opened-assets table tbody tr, fingather-opened-grouped-assets table tbody tr').first().isVisible({ timeout: 15000 }).catch(() => false);
    }

    async expectDetailVisible(): Promise<void> {
        await expect(this.page).toHaveURL(/\/assets\/\d+/);
        await expect(this.page.locator('fingather-asset-value')).toBeVisible({ timeout: 10000 });
    }
}
