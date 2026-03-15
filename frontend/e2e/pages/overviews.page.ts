import { expect, Page } from '@playwright/test';

export class OverviewsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/overviews');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/overviews');
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 15000 });
    }

    async expectTableVisible(): Promise<void> {
        await expect(this.page.locator('table')).toBeVisible({ timeout: 15000 });
    }

    async hasYearRows(): Promise<boolean> {
        await this.page.waitForSelector('table', { timeout: 15000 });
        const count = await this.page.locator('table tbody tr').count();
        return count > 0;
    }

    async clickFirstTaxReport(): Promise<void> {
        await this.page.waitForSelector('a[href*="tax-report"]', { timeout: 15000 });
        await this.page.locator('a[href*="tax-report"]').first().click();
    }

    async expectTaxReportLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/overviews\/tax-report\/\d+/, { timeout: 10000 });
        await expect(this.page.locator('.card').first()).toBeVisible({ timeout: 10000 });
    }
}
