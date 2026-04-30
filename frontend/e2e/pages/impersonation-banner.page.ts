import { expect, Page } from '@playwright/test';

export class ImpersonationBannerPage {
    constructor(private readonly page: Page) {}

    private banner() {
        return this.page.locator('[data-testid="impersonation-banner"]');
    }

    async expectVisible(targetEmail: string): Promise<void> {
        await expect(this.banner()).toBeVisible({ timeout: 10000 });
        await expect(this.banner()).toContainText(targetEmail);
    }

    async expectHidden(): Promise<void> {
        await expect(this.banner()).toHaveCount(0, { timeout: 10000 });
    }

    async switchBack(): Promise<void> {
        await this.banner().locator('[data-testid="switch-back-button"]').click();
    }
}
