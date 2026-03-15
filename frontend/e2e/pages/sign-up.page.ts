import { Page } from '@playwright/test';

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
}
