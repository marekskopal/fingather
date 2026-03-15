import { expect, Page } from '@playwright/test';

export class WatchlistPage {
    constructor(private readonly page: Page) {}

    async gotoAssets(): Promise<void> {
        await this.page.goto('/assets');
        await this.page.waitForSelector('ul[role="tablist"]', { timeout: 10000 });
    }

    async clickWatchlistTab(): Promise<void> {
        await this.page.locator('ul[role="tablist"] button', { hasText: 'Watch list' }).click();
    }

    async expectWatchlistVisible(): Promise<void> {
        await expect(this.page.locator('fingather-watched-assets')).toBeVisible({ timeout: 10000 });
    }

    async gotoAddAsset(): Promise<void> {
        await this.page.goto('/assets/add-asset');
        await this.page.waitForSelector('form', { timeout: 10000 });
    }

    async expectAddAssetFormLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/assets/add-asset');
        await expect(this.page.locator('fingather-ticker-search-selector')).toBeVisible({ timeout: 10000 });
    }

    async searchAndSelectTicker(search: string): Promise<void> {
        // Open the ticker search dropdown (toggle button has id="tickerId")
        await this.page.locator('fingather-ticker-search-selector button').first().click();

        // Search input inside dropdown has id="tickerId-ticker-search"
        const searchInput = this.page.locator('input#tickerId-ticker-search');
        await searchInput.fill(search);
        await this.page.waitForTimeout(600); // Wait for debounce

        // Click first result
        await this.page.locator('[aria-labelledby="tickerId"] button.dropdown-item').first().click();
    }

    async submitAddAssetForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async getWatchlistRowCount(): Promise<number> {
        await this.expectWatchlistVisible();
        return this.page.locator('fingather-watched-assets table tbody tr').count();
    }
}
