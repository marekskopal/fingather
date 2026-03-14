import { expect, Page } from '@playwright/test';

export class LoginPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/authentication/login');
    }

    async login(email: string, password: string): Promise<void> {
        await this.page.fill('#email', email);
        await this.page.fill('#password', password);
        await this.page.getByRole('button', { name: 'Login' }).click();
    }

    async expectLoginError(): Promise<void> {
        await expect(this.page.locator('.alert-danger, .alert.alert-danger, [role="alert"]')).toBeVisible();
    }

    async expectRedirectedToLogin(): Promise<void> {
        await expect(this.page).toHaveURL(/\/authentication\/login/);
    }
}
