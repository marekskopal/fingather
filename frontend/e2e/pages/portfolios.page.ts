import { expect, Page } from '@playwright/test';

export class PortfoliosPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/portfolios');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/portfolios');
        await expect(this.page.locator('.portfolio-list')).toBeVisible({ timeout: 10000 });
    }

    /** Check that a portfolio card with the given name is visible in the list. */
    async expectPortfolioVisible(name: string): Promise<void> {
        await expect(this.page.locator('.portfolio-list .portfolio h2.h4', { hasText: name })).toBeVisible({ timeout: 10000 });
    }

    async getPortfolioCount(): Promise<number> {
        await this.page.waitForSelector('.portfolio-list', { timeout: 10000 });
        return this.page.locator('.portfolio-list .portfolio').count();
    }

    async gotoAddPortfolio(): Promise<void> {
        await this.page.goto('/portfolios/add-portfolio');
        await this.page.waitForSelector('input#name', { timeout: 10000 });
    }

    async expectAddFormLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/portfolios/add-portfolio');
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('input#name')).toBeVisible();
    }

    async fillName(name: string): Promise<void> {
        await this.page.locator('input#name').fill(name);
    }

    async selectFirstCurrency(): Promise<void> {
        const component = this.page.locator('fingather-select#currencyId');
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').first().click();
    }

    async submitForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async expectRedirectedToList(): Promise<void> {
        await expect(this.page).toHaveURL('/portfolios', { timeout: 10000 });
    }

    async clickEditPortfolio(name: string): Promise<void> {
        const portfolioCard = this.page.locator('.portfolio').filter({ hasText: name });
        await portfolioCard.locator('a[href*="edit-portfolio"]').click();
    }

    async deletePortfolio(name: string): Promise<void> {
        const portfolioCard = this.page.locator('.portfolio').filter({ hasText: name });
        await portfolioCard.locator('fingather-delete-button button').click();
        await expect(this.page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await this.page.locator('.modal-footer button.btn-danger').click();
        // Wait for modal to close and portfolio to disappear
        await expect(this.page.locator('.portfolio-list .portfolio h2.h4', { hasText: name })).not.toBeVisible({ timeout: 10000 });
    }
}
