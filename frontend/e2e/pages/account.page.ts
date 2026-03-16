import {expect, Page} from '@playwright/test';

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

    async fillPassword(password: string): Promise<void> {
        await this.page.fill('#password', password);
    }

    async expectRequirementsVisible(): Promise<void> {
        await expect(this.page.locator('fingather-password-requirements .requirements-list')).toBeVisible();
    }

    async expectRequirementsHidden(): Promise<void> {
        await expect(this.page.locator('fingather-password-requirements .requirements-list')).toBeHidden();
    }

    async expectAllRequirementsMet(): Promise<void> {
        const items = this.page.locator('fingather-password-requirements .requirements-list li');
        const count = await items.count();
        for (let i = 0; i < count; i++) {
            await expect(items.nth(i)).toHaveClass(/met/);
        }
    }

    async submitEdit(): Promise<void> {
        await this.page.getByRole('button', { name: 'Save' }).click();
    }

    async expectPasswordError(): Promise<void> {
        await expect(this.page.locator('fingather-input-validator').filter({ hasText: /requirements/i })).toBeVisible();
    }
}
