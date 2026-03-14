import { expect, Page } from '@playwright/test';

export class DashboardPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page.locator('fingather-portfolio-total, [class*="portfolio-total"]')).toBeVisible();
    }

    async expectPortfolioSectionVisible(): Promise<void> {
        await expect(this.page.locator('fingather-portfolio-total')).toBeVisible();
    }

    async expectGroupDataVisible(): Promise<void> {
        await expect(this.page.locator('fingather-group-data')).toBeVisible();
    }
}
