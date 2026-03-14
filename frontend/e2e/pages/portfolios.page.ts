import { expect, Page } from '@playwright/test';

export class PortfoliosPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/portfolios');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/portfolios');
    }

    async expectPortfolioVisible(name: string): Promise<void> {
        await expect(this.page.getByText(name)).toBeVisible();
    }

    async openAddModal(): Promise<void> {
        await this.page.click('a[routerlink*="add"], a[href*="/portfolios/add"], button:has-text("Add"), mat-icon:has-text("add")');
    }

    async fillPortfolioForm(name: string): Promise<void> {
        await this.page.fill('input[formcontrolname="name"], input[id="name"]', name);
    }

    async submitForm(): Promise<void> {
        await this.page.click('button[type="submit"], fingather-save-button button');
    }

    async deletePortfolio(name: string): Promise<void> {
        const row = this.page.locator('tr, .portfolio-item').filter({ hasText: name });
        await row.locator('fingather-delete-button button, button[aria-label*="delete"], button:has-text("Delete")').click();
        // Confirm dialog if present
        const confirmBtn = this.page.locator('.modal button:has-text("Delete"), .modal button:has-text("Confirm")');
        if (await confirmBtn.isVisible()) {
            await confirmBtn.click();
        }
    }
}
