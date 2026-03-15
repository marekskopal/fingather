import { expect, Page } from '@playwright/test';

export class DividendsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/dividends');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/dividends');
        await expect(this.page.locator('ul.nav.nav-tabs').first()).toBeVisible({ timeout: 10000 });
    }

    async expectHistoryTabActive(): Promise<void> {
        await expect(this.page.locator('fingather-dividends-data-chart')).toBeVisible({ timeout: 15000 });
    }

    async clickForecastTab(): Promise<void> {
        // The outer History/Forecast nav is the first ul.nav.nav-tabs; inner range tabs come after
        await this.page.locator('ul.nav.nav-tabs').first().locator('a.nav-link').nth(1).click();
    }

    async expectForecastVisible(): Promise<void> {
        // The @if(activeTab()==='forecast') block adds the component to the DOM when the tab is active
        await this.page.locator('fingather-dividend-forecast-calendar').waitFor({ state: 'attached', timeout: 10000 });
    }

    async expectTransactionListVisible(): Promise<void> {
        await expect(this.page.locator('fingather-transaction-list')).toBeVisible({ timeout: 15000 });
    }
}
