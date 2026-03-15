import { expect, Page } from '@playwright/test';

export class GoalsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/goals');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/goals');
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async gotoAddGoal(): Promise<void> {
        await this.page.goto('/goals/add-goal');
        await this.page.waitForSelector('fingather-select#portfolioId', { timeout: 10000 });
    }

    async expectAddFormLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/goals/add-goal');
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('fingather-select#portfolioId')).toBeVisible();
        await expect(this.page.locator('fingather-select#type')).toBeVisible();
        await expect(this.page.locator('input#targetValue')).toBeVisible();
        await expect(this.page.locator('fingather-date-input#deadline')).toBeVisible();
    }

    private async selectFirstInDropdown(id: string): Promise<void> {
        const component = this.page.locator(`fingather-select#${id}`);
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').first().click();
    }

    async selectFirstPortfolio(): Promise<void> {
        await this.selectFirstInDropdown('portfolioId');
    }

    async selectFirstType(): Promise<void> {
        await this.selectFirstInDropdown('type');
    }

    async fillTargetValue(value: string): Promise<void> {
        await this.page.locator('input#targetValue').fill(value);
    }

    async fillDeadline(date: string): Promise<void> {
        const input = this.page.locator('fingather-date-input#deadline input');
        await input.click();
        await input.evaluate((el: HTMLInputElement, val: string) => {
            el.type = 'date';
            el.value = val;
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }, date);
    }

    async submitForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async expectRedirectedToList(): Promise<void> {
        await expect(this.page).toHaveURL('/goals', { timeout: 10000 });
    }

    async getGoalRowCount(): Promise<number> {
        await this.page.waitForSelector('table', { timeout: 10000 });
        return this.page.locator('table tbody tr').count();
    }

    async clickEditFirst(): Promise<void> {
        await this.page.waitForSelector('table tbody tr a[href*="edit-goal"]', { timeout: 10000 });
        await this.page.locator('table tbody tr a[href*="edit-goal"]').first().click();
    }

    async clickDeleteFirst(): Promise<void> {
        await this.page.waitForSelector('fingather-delete-button button', { timeout: 10000 });
        await this.page.locator('fingather-delete-button button').first().click();
    }

    async confirmDelete(): Promise<void> {
        await expect(this.page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await this.page.locator('.modal-footer button.btn-danger').click();
    }
}
