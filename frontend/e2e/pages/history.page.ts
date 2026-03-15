import { expect, Page } from '@playwright/test';

export class HistoryPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/history');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/history');
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async expectChartVisible(): Promise<void> {
        await expect(this.page.locator('fingather-portfolio-value-chart')).toBeVisible({ timeout: 10000 });
    }

    async expectRangeTabsVisible(): Promise<void> {
        await expect(this.page.locator('ul.nav.nav-tabs')).toBeVisible({ timeout: 10000 });
    }
}
