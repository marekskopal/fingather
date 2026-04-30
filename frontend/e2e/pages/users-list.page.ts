import { expect, Page } from '@playwright/test';

export class UsersListPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/users');
        await expect(this.page.locator('table')).toBeVisible({ timeout: 10000 });
    }

    private rowFor(email: string) {
        return this.page.locator('tbody tr').filter({ hasText: email });
    }

    async switchToFor(email: string): Promise<void> {
        this.page.once('dialog', async (dialog) => {
            await dialog.accept();
        });
        await this.rowFor(email)
            .locator('[data-testid="switch-to-button"]')
            .click();
    }

    async expectNoSwitchToFor(email: string): Promise<void> {
        await expect(
            this.rowFor(email).locator('[data-testid="switch-to-button"]'),
        ).toHaveCount(0);
    }

    async expectSwitchToFor(email: string): Promise<void> {
        await expect(
            this.rowFor(email).locator('[data-testid="switch-to-button"]'),
        ).toBeVisible();
    }
}
