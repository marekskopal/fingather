import {expect, Page} from '@playwright/test';

export class SignUpPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/authentication/sign-up');
    }

    async signUp(name: string, email: string, password: string): Promise<void> {
        await this.page.fill('#name', name);
        await this.page.fill('#email', email);
        await this.page.fill('#password', password);
        await this.page.getByRole('button', { name: 'Sign Up' }).click();
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

    async expectRequirementMet(index: number): Promise<void> {
        await expect(this.page.locator('fingather-password-requirements .requirements-list li').nth(index)).toHaveClass(/met/);
    }

    async expectRequirementUnmet(index: number): Promise<void> {
        await expect(this.page.locator('fingather-password-requirements .requirements-list li').nth(index)).toHaveClass(/unmet/);
    }

    async expectAllRequirementsMet(): Promise<void> {
        const items = this.page.locator('fingather-password-requirements .requirements-list li');
        const count = await items.count();
        for (let i = 0; i < count; i++) {
            await expect(items.nth(i)).toHaveClass(/met/);
        }
    }

    async submitForm(): Promise<void> {
        await this.page.getByRole('button', { name: 'Sign Up' }).click();
    }

    async expectPasswordError(): Promise<void> {
        await expect(this.page.locator('input#password')).toHaveClass(/is-invalid/);
    }
}
