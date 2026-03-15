import { expect, Page } from '@playwright/test';

export class AccountPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/account');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/account');
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async expectInfoTableVisible(): Promise<void> {
        await expect(this.page.locator('table')).toBeVisible({ timeout: 10000 });
    }

    async clickEdit(): Promise<void> {
        await this.page.locator('button.btn-primary').first().click();
    }

    async expectEditFormVisible(): Promise<void> {
        await expect(this.page.locator('input#name')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('input#email')).toBeVisible();
        await expect(this.page.locator('input#password')).toBeVisible();
    }

    async clickCancelEdit(): Promise<void> {
        await this.page.locator('button.btn-secondary').click();
    }

    async expectInfoViewVisible(): Promise<void> {
        await expect(this.page.locator('table')).toBeVisible({ timeout: 5000 });
    }
}
